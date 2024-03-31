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
use App\Services\LocationService;
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
    #[Route('/unsubscribe/{id}', name: 'app_event_unsubscription', methods: ['GET','POST'])]
    public function eventUnSubscribe(int $id, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $event = $eventRepository->find($id);

        //Verif si l'event existe
        if (!$event)
        {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas');
        }

        //Verif état ouvert et si date limite pas dépassé
        if ($event->getState()== State::OUVERTE)
        {
            $event->removeParticipate($user);
            $entityManager->flush();

            //Refresh si clic depuis l'event list
            $source = $request->query->get('source');
            if ($source === 'eventIndex') {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id'=>$id]));
            }

            return $this->redirectToRoute('app_event_show', ['id'=>$id]);
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
            throw $this->createNotFoundException('La date limite d\'inscription est dépassée');
        }

        // verifier si le nombre place maximum n'est pas atteint
        if ($event->getParticipate()->count() >= $event->getMaxRegistration())
        {
            throw $this->createNotFoundException('Le nombre limite de participant est atteint');
        }

        //Vérif si état "ouvert" et si organisateur
        if ($event->getState()== State::OUVERTE)
        {
            $event->addParticipate($user);
            $entityManager->flush();

            //Refresh si clic depuis l'event list
            $source = $request->query->get('source');
            if ($source === 'eventIndex') {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id'=>$id]));
            }

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
            'campus' => $campusRepository->findAll(),
            'selectedCampus' => $campusId,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LocationService $locationService, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event, [
            'displayDeleteButton' => false,
        ]);
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

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,

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
    public function publish(Event $event, EntityManagerInterface $entityManager): Response
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

            return $this->redirectToRoute('app_event_index');
        }
        return $this->redirectToRoute('app_event_index');
    }

    #[Route('/{id}/cancel', name: 'app_event_cancel', methods: ['GET'])]
    public function cancel(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($event->getEventOrganizer() != $user ) {
            return $this->redirectToRoute('app_event_index');
        }

        //Vérifie sur l'état est bien ouverte.
        if ($event->getState() == State::OUVERTE)
        {
            $event->setState(State::ANNULEE);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index');
        }
        return $this->redirectToRoute('app_event_index');
    }

}
