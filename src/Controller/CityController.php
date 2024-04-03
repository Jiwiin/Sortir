<?php

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class CityController extends AbstractController
{
    #[Route('/', name: 'app_city')]
    public function index(): Response
    {
        return $this->render('city/index.html.twig', [
            'controller_name' => 'CityController',
        ]);
    }

    #[Route('/city', name: 'app_city_show', methods: ['GET', 'POST'])]
    public function show(CityRepository $cityRepository): Response {
        $cities = $cityRepository->findAll();
        return $this->render('city/show.html.twig',  [
            'cities' => $cities,
        ]); 
    }
}
