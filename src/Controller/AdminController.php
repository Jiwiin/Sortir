<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/users', name: 'app_admin_show',  methods: ['GET','POST'])]
    public function show(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/show.html.twig',  [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}', name: 'app_admin_edit',  methods: ['GET','POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_show',Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
