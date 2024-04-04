<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class CityController extends AbstractController
{
    #[Route('/', name: 'app_city', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('city/index.html.twig', [
            'controller_name' => 'CityController',
        ]);
    }

    #[Route('/city', name: 'app_city_show', methods: ['GET'])]
    public function show(CityRepository $cityRepository): Response {
        $cities = $cityRepository->findAll();
        return $this->render('city/show.html.twig',  [
            'cities' => $cities,
        ]); 
    }

    // #[Route('/city/{id}', name: 'app_city_edit',  methods: ['GET','POST'])]
    // public function edit(Request $request, City $city, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(AdminUserType::class, $city);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {

    //         $entityManager->flush();
    //         $this->addFlash('success', 'Les modifications du profil ont été enregistrées.' );

    //         return $this->redirectToRoute('app_city_show');
    //     }

    //     return $this->render('city/show.html.twig', [
    //         'city' => $city,
    //         'form' => $form,
    //     ]);
    // }
}
