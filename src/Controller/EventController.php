<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Enum\State;
use App\Form\CancelEventType;
use App\Form\EventType;
use App\Form\LocationType;
use App\Form\SearchForm;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Services\LocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, EntityManagerInterface $entityManager,  CampusRepository $campusRepository, Request $request): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $eventRepository->updateEventState();

        /** @var User $user */
        //Vérifie si l'utilisateur connecté est une instance de User afin de récupérer son campus
        $user = $this->getUser();

        // filtre form
        $data = new SearchData();
        $form = $this->createForm(SearchForm::class, $data, [ 'selectedCampus' => $user->getCampus()]);
        $form->handleRequest($request);

        //Récupère l'id du campus envoyé dans l'url
        $campusId = $request->query->get('campus');
        if(!empty($campusId)) {
            $campus = $entityManager->getRepository(Campus::class)->find($campusId);
            if($campus !== null) {
                $data->campus = $campus;
            }
        } else {
            $data->campus = $user->getCampus();
        }

        //Ajout de l'utilisateur connecté dans le SearchData
        $data->user = $user;
        $events = $eventRepository->findSearch($data);

        return $this->render('event/index.html.twig', [
            'events' => $events,
            /*'campus' => $campusRepository->findAll(),*/
            'selectedCampus' => $campusId,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/unsubscribe/{id}', name: 'app_event_unsubscription', methods: ['GET','POST'])]
    public function eventUnSubscribe(int $id, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $event = $eventRepository->find($id);
        $currentDateTime = new \DateTime();

        //Verif si l'event existe
        if (!$event)
        {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas');
        }


        //Verif état ouvert ou cloturée
        if ($event->getState()== State::OUVERTE || $event->getState()== State::CLOTURE)
        {
            $event->removeParticipate($user);
            $entityManager->flush();
            //Verif si le nombre de participant est inferieur au max
            if ($event->getParticipate()->count() < $event->getMaxRegistration())
            {
                if($event->getState()== State::CLOTURE)
                {
                    $event->setState(State::OUVERTE);
                }
                $entityManager->flush();
            }

            //Refresh si clic depuis l'event list
            $source = $request->query->get('source');
            if ($source === 'eventIndex') {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id'=>$id]));
            }

            $this->addFlash('success', 'Vous n\'êtes plus inscrit à cet evenement.');
            return $this->redirectToRoute('app_event_show', ['id'=>$id]);
        }
        else
        {
            $this->addFlash('danger', 'Vous ne pouvez plus vous désincrire.');
        }

        return $this->redirectToRoute('app_event_show', ['id'=>$id]);
    }


    #[Route('/subscribe/{id}', name: 'app_event_subscription', methods: ['GET','POST'])]
    public function eventSubscribe(int $id, Request $request,EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'ID
        /** @var User $user */
        $user = $this->getUser();
        $currentDateTime = new \DateTime();
        $event = $eventRepository->find($id);

        //Verif si l'event existe
        if (!$event)
        {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas');
        }

        // Verif si la date limite d'inscription n'est pas dépassé
        if ($event->getDateLimitRegistration() < $currentDateTime)
        {
            $this->addFlash('danger', 'La date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('app_event_show', ['id'=>$id]);
        }

        // verifier si le nombre place maximum n'est pas atteint
        if ($event->getParticipate()->count() >= $event->getMaxRegistration())
        {
            $this->addFlash('danger', 'Le nombre limite de participants est atteint.');
            return $this->redirectToRoute('app_event_show', ['id'=>$id]);
        }

        //Vérif si état "ouvert" et si organisateur
        if ($event->getState()== State::OUVERTE)
        {
            $event->addParticipate($user);
            $entityManager->flush();
            if ($event->getParticipate()->count() == $event->getMaxRegistration())
            {
                $event->setState(State::CLOTURE);
                $entityManager->flush();
            }

            //Refresh si clic depuis l'event list
            $source = $request->query->get('source');
            if ($source === 'eventIndex') {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id'=>$id]));
            }

            $this->addFlash('success', 'Inscription réussie à l\'évènement.');
            return $this->redirectToRoute('app_event_show', ['id'=>$id]);
        }
        else
        {
            $this->addFlash('danger', 'L\'évènement n\'est plus disponible.');
        }

        return $this->redirectToRoute('app_event_show', ['id'=>$id]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LocationService $locationService, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $location = new Location();
        $formLocation = $this->createForm( LocationType::class, $location);
        $form = $this->createForm(EventType::class, $event, [
            'displayDeleteButton' => false,
        ]);
        $formLocation->handleRequest($request);
        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('save')->isClicked()){
                $event->setState(State::EN_CREATION);
            } elseif($form->get('publish')->isClicked()) {
                $event->setState(State::OUVERTE);
            }

            $event->setEventOrganizer($user);
            $event->addParticipate($user);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'L\'événement a été créé.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($formLocation->isSubmitted() && $formLocation->isValid())
        {
            $entityManager->persist($location);
            $entityManager->flush();
            $this->addFlash('success', 'Le lieu a été ajouté.');
            return $this->redirectToRoute('app_event_new');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,
            'formLocation' => $formLocation,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($event->getEventOrganizer() != $user ) {
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        //Récupère la liste des adresses
        $locations = $entityManager->getRepository(Location::class)->findAll();
        $locationsData = [];
        foreach($locations as $location) {
            $locationsData[$location->getId()] = [
                'street' => $location->getStreet(),
                'zipcode' => $location->getCity()->getZipcode(),
            ];
        }


        $form = $this->createForm(EventType::class, $event, [
            'displayDeleteButton' => true,
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->get('delete')->isClicked()) {
                return $this->redirectToRoute('app_event_delete', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
            'locationsData' => json_encode($locationsData),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_event_delete', methods: ['GET'])]
    public function delete(int $id,Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($event->getEventOrganizer() != $user ) {
            return $this->redirectToRoute('app_event_index');
        }

        //Vérifie sur l'état est bien en création.
        if ($event->getState() == State::EN_CREATION)
        {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('danger', 'L\'événement a été supprimé.');

            //Refresh si clic depuis l'event list
            $source = $request->query->get('source');
            if ($source === 'eventIndex') {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id'=>$id]));
            }

            return $this->redirectToRoute('app_event_index');
        }
        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/publish', name: 'app_event_publish', methods: ['GET'])]
    public function publish(Event $event, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($event->getEventOrganizer() != $user ) {
            return $this->redirectToRoute('app_event_index');
        }

        //Vérifie sur l'état est bien en création.
        if ($event->getState() == State::EN_CREATION)
        {
            $event->setState(State::OUVERTE);
            $entityManager->flush();
            $this->addFlash('success', 'L\'événement a été publié, les utilisateurs peuvent maintenant s\'inscrire.');
            //Refresh si clic depuis l'event list
            $source = $request->query->get('source');
            if ($source === 'eventIndex') {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id'=>$id]));
            }
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('app_event_index');
    }

    #[Route('/{id}/cancel', name: 'app_event_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $event = $eventRepository->find($id);
        $eventInfo = $event->getEventInfo();

        if($event->getEventOrganizer() != $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_event_index');
        }

        $form = $this->createForm(CancelEventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted()) {

            $info = $form->get('eventInfo')->getData();
            $eventCancelInfo = $info . ' [CANCEL] ' . $eventInfo;
            $event->setEventInfo($eventCancelInfo);
            $event->setState(State::ANNULEE);
            $entityManager->flush();
            $this->addFlash('danger', 'L\'événement a été annulé.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/cancel.html.twig', [
            'cancelEventForm'=> $form->createView(),
            'event'=> $event
        ]);
    }

    #[Route('/get-events/{eventList}', name:'get_event_list', methods: ['GET'])]
    public function getEventList(EventRepository $eventRepository, Request $request): JsonResponse
    {
        // Récupérer le paramètre d'état de la requête
        $state = $request->query->get('state');

        // Vérifier si l'état est valide
        $allowedStates = ['ouverte', 'en cours', 'historisée', 'annulée', 'cloturée'];
        if ($state && !in_array($state, $allowedStates))
        {
            return new JsonResponse(['error' => 'État non valide'], 400);
        }

        // Récupérer les événements en fonction de l'état spécifié
        $eventList = $eventRepository->findBy(['state' => $state ?: $allowedStates]);

        // Construit la liste des événements à retourner en format JSON
        $eventListArray = [];
        foreach ($eventList as $event) {
            $eventListArray[] = [
                'id' => $event->getId(),
                'name' => $event->getName(),
                'dateStart' => $event->getDateStart(),
                'duration' => $event->getDuration(),
                'dateLimitRegistration' => $event->getDateLimitRegistration(),
                'maxRegistration' => $event->getMaxRegistration(),
                'state' => $event->getState(),
                'locationId' => $event->getLocation()->getId(),
                'eventOrganizerId' => $event->getEventOrganizer()
            ];
        }

        return new JsonResponse($eventListArray);
    }

}
