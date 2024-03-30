<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function edit(UserRepository $userRepository): Response
    {
        return $this->render('admin/edit.html.twig');
    }
}
