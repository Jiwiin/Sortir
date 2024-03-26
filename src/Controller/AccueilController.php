<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route("/accueil", name: 'accueil_home')]
    public function home()
    {
        return $this->render("accueil/accueil.html.twig");
    }
}