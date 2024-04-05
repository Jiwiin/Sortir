<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/campus', name: 'app_campus_show', methods: ['GET', 'POST'])]
    public function show(CampusRepository $campusRepository,Request $request,EntityManagerInterface $entityManager): Response {
        $schools = $campusRepository->findAll();
        $campus = new Campus();
        $form= $this->createForm( CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();
            $this->addFlash('success', 'Le campus a été créé');
            return $this->redirectToRoute('app_campus_show');
        }
        return $this->render('campus/show.html.twig',  [
            'schools' => $schools,
            'form' => $form,
        ]); 
    }

    #[Route('/{id}/delete-campus', name: 'app_campus_delete', methods: ['GET'])]
    public function delete(int $id, CampusRepository $campusRepository, EntityManagerInterface $entityManager): Response {

        $campus = $campusRepository->find($id);
        if($campus) {
            $entityManager->remove($campus);
            $entityManager->flush();
            $this->addFlash('success', 'Le campus a été supprimé');
        } else {
            $this->addFlash('danger', 'Le campus n\'existe pas');
        }
        return $this->redirectToRoute('app_campus_show');
    }
}
