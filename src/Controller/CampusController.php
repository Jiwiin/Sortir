<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class CampusController extends AbstractController
{
    #[Route('/', name: 'app_campus', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
        ]);
    }

    #[Route('/campus', name: 'app_campus_show', methods: ['GET'])]
    public function show(CampusRepository $campusRepository): Response {
        $schools = $campusRepository->findAll();
        return $this->render('campus/show.html.twig',  [
            'schools' => $schools,
        ]); 
    }
}
