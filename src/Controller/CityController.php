<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/city', name: 'app_city_show', methods: ['GET', 'POST'])]
    public function show(Request $request, CityRepository $cityRepository, EntityManagerInterface $entityManager): Response {
        $cities = $cityRepository->findAll();
        $city = new City();
        $form= $this->createForm( CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();
            $this->addFlash('success', 'La ville a été créée');
            return $this->redirectToRoute('app_city_show');
        }


        return $this->render('city/show.html.twig',  [
            'cities' => $cities,
            'form' => $form,
        ]); 
    }

    #[Route('/{id}/delete-city', name: 'app_city_delete', methods: ['GET'])]
    public function delete(int $id, CityRepository $cityRepository, EntityManagerInterface $entityManager): Response {

        $city = $cityRepository->find($id);
        if($city) {
            $entityManager->remove($city);
            $entityManager->flush();
            $this->addFlash('success', 'La ville a été supprimée');
        } else {
            $this->addFlash('danger', 'La ville n\'existe pas');
        }
        return $this->redirectToRoute('app_city_show');
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
