<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events', name: 'events_')]
class EventController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(EventRepository $eventRepository, CampusRepository $campusRepository, Request $request): Response
    {
        $campusId = $request->query->get('campus');
        return $this->render('event/list.html.twig', [
            "events" => $events,
        ]);
    }
}
