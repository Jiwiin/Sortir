<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // Vérifie si le profil de l'utilisateur est le même que celui consulté
        $isOwnProfile = $this->getUser() === $user;

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'isOwnProfile' => $isOwnProfile,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        if (!$this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }
        if($this->getUser() !== $user)
        {
            return $this->redirectToRoute('app_event_index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            if($form->isSubmitted() && $form->isValid()){
                $profilePictureFile = $form->get('profilePicture')->getData();

                if ($profilePictureFile){
                    $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$profilePictureFile->guessExtension();

                    try {
                        $profilePictureFile->move(
                            $this->getParameter('uploads_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $user->setProfilePicture($newFilename);
                    }
                }
            }
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de votre profil ont bien été modifiées.');
            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);

    }

    #[Route('/edit/edit-password/{id}', name: 'app_user_edit_password', methods: ['GET', 'POST'])]
    public function editPassword(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if($userPasswordHasher->isPasswordValid($user, $form->getData()['plainPassword']))
            {
                $user->setPassword($userPasswordHasher->hashPassword($user, $form->getData()['newPassword']));

                $this->addFlash('success', 'Le mot de passe à été modifié');
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            }
            else
            {
                $this->addFlash('danger','Le mot de passe renseigné est incorrect');
                return $this->redirectToRoute('app_user_edit_password', ['id' => $user->getId()]);
            }

        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form,
            'user'=>$user
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
