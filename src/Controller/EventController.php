<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Enum\State;
use App\Form\EventType;
use App\Form\SearchForm;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\throwException;


#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/subscribe/{id}', name: 'app_event_subscription', methods: ['GET','POST'])]
    public function eventInscription(int $id, EventRepository $eventRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'ID
        /** @var User $user */
        $user = $this->getUser();
        $currentDateTime = new \DateTime();

        // Vérifier si l'événement est en etat "ouvert"
        $event = $eventRepository->find($id);
        if (!$event)
        {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas');
        }

        // Verifier si la date limite d'inscription ne soit pas dépassé
        if ($event->getDateLimitRegistration() < $currentDateTime)
        {
            throw $this->createNotFoundException('La date limite d\'inscription est dépassée');
        }

        // verifier si le nombre place maximum n'est pas atteint
        if ($event->getParticipate()->count() >= $event->getMaxRegistration())
        {
            throw $this->createNotFoundException('Le nombre limite de participant est atteint');
        }

        //Vérif si état "ouvert" et si organisateur
        if ($event->getState()== State::OUVERTE && $event->getEventOrganizer() !== $user )
        {
            $event->addParticipate($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_show', ['id'=>$id]);
        }


   return $this->redirectToRoute('app_event_show', ['id'=>$id]);
}


    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, EntityManagerInterface $entityManager,  CampusRepository $campusRepository, Request $request): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // filtre form
        $data = new SearchData();
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);

        /** @var User $user */
        //Vérifie si l'utilisateur connecté est une instance de User afin de récupérer son campus
        $user = $this->getUser();

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
            'campus' => $campusRepository->findAll(),
            'selectedCampus' => $campusId,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        //Récupère la liste des adresses
        $locations = $entityManager->getRepository(Location::class)->findAll();
        $locationsData = [];
        foreach($locations as $location) {
            $locationsData[$location->getId()] = [
                'street' => $location->getStreet(),
                'zipcode' => $location->getCity()->getZipcode(),
            ];
        }

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

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
            'locationsData' => json_encode($locationsData),
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


        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

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

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
