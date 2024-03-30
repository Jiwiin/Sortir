<?php

namespace App\Controller;

use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/location')]
class LocationController extends AbstractController
{
    #[Route('/', name: 'app_location_index', methods: ['GET'])]
    public function index(LocationRepository $locationRepository): Response
    {
        return $this->render('location/index.html.twig', [
            'locations' => $locationRepository->findAll(),
        ]);
    }

    #[Route('/get-locations/{cityId}', name:'get_locations_for_city', methods: ['GET'])]
    public function getLocationsForCity($cityId, LocationRepository $locationRepository): Response
    {
        //Récupère toutes les villes et les lieux
        $locations = $locationRepository->findByCityId($cityId);
        $locationsArray = [];

        foreach ($locations as $location) {
            $locationsArray[] = [
                'id' => $location->getId(),
                'name' => $location->getName(),
                'street' => $location->getStreet(),
                'zipcode' => $location->getCity()->getZipCode(),
            ];
        }
        return new JsonResponse($locationsArray);
    }


}
