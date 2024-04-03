<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminCreateUserType;
use App\Form\AdminUserType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        $user = $userRepository->findAll();
        return $this->render('admin/show.html.twig',  [
            'users' => $user,
        ]);
    }

    #[Route('/users/{id}', name: 'app_admin_edit',  methods: ['GET','POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            $this->addFlash('success', 'Les modifications du profil ont été enregistrées.' );

            return $this->redirectToRoute('app_admin_show');
        }

        return $this->render('admin/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/create-user', name: 'app_admin_create_user',  methods: ['GET','POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(AdminCreateUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($userPasswordHasher->hashPassword($user, 'azerty'));
            $user->setRoles(['ROLE_USER']);
            $user->setStatus(true);
            $user->setProfilePicture('sortir.png');
            $entityManager->persist($user);
            $entityManager->flush();

           $this->addFlash('success', 'L\'utilisateur à été ajouté.' );
            return $this->redirectToRoute('app_admin_show');
        }

        return $this->render('admin/create-user.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
