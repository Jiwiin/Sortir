<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\User;
use App\Form\AdminCreateUserType;
use App\Form\AdminUserType;
use App\Form\CSVUploadFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
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
        $formCSV = $this->createForm(CSVUploadFormType::class);
        $form = $this->createForm(AdminCreateUserType::class, $user);
        $formCSV->handleRequest($request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($userPasswordHasher->hashPassword($user, 'azerty'));
            $user->setRoles(['ROLE_USER']);
            $user->setStatus(true);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur à été ajouté.' );
            return $this->redirectToRoute('app_admin_show');
        }

        if ($formCSV->isSubmitted() && $formCSV->isValid())
        {
            $csvFile = $formCSV['csvFile']->getData();
            if ($csvFile) {
                $csvFile = Reader::createFromPath($csvFile->getRealPath(), 'r');
                $csvFile->setHeaderOffset(0);
                $stmt = (new Statement());
                $records = $stmt->process($csvFile);
                foreach ($records as $record) {
                    $user = new User();
                    $user->setEmail($record['email']);
                    $user->setUsername($record['username']);
                    $user->setFirstname($record['firstname']);
                    $user->setPassword($userPasswordHasher->hashPassword($user, 'azerty'));
                    $user->setRoles(['ROLE_USER']);
                    $user->setLastname($record['lastname']);
                    $user->setPhone($record['phone']);
                    $user->setCampus($entityManager->getRepository(Campus::class)->find($record['campus_id']));
                    $user->setStatus(true);
                    $entityManager->persist($user);
                }
                $entityManager->flush();
            }

            $this->addFlash('success', 'La liste à été ajouté.' );
            return $this->redirectToRoute('app_admin_show');
        }

        return $this->render('admin/create-user.html.twig', [
            'user' => $user,
            'form' => $form,
            'formCSV' => $formCSV,
        ]);
    }

    #[Route('/{id}/delete-user', name: 'app_admin_delete_user', methods: ['GET'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash("danger", "L'utilisateur n'existe pas");
            return $this->redirectToRoute('app_admin_show');
        }

        $userCurrent = $this->getUser();
        if (!in_array('ROLE_ADMIN', $userCurrent->getRoles(), true)) {
            $this->addFlash("danger", "Action non autorisée");
            return $this->redirectToRoute('app_event_index');
        }


        if (!$user->getEvents()->isEmpty() || !$user->getParticipationEvents()->isEmpty()) {
            $this->addFlash("danger", "L'utilisateur participe à une ou plusieurs sorties et ne peut pas être supprimé");
            return $this->redirectToRoute('app_admin_show');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash("success", "L'utilisateur a été supprimé");
        return $this->redirectToRoute('app_admin_show');
    }
}
