<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


class MainController extends AbstractController
{
    #[Route("/", name: 'main_home')]
    public function home()
    {
        return $this->redirectToRoute('app_event_index');
    }
}