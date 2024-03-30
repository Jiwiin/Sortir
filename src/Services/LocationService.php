<?php

namespace App\Services;

use App\Repository\LocationRepository;

class LocationService
{

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }
    public function getAllLocationsAndCities(): array
    {
        //Récupère toutes les villes et les lieux
        $locations = $this->locationRepository->findAll();
        $locationsList = [];

        foreach ($locations as $location) {
            $locationsList[] = [
                'id' => $location->getId(),
                'name' => $location->getName(),
                'street' => $location->getStreet(),
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
                'city' => [
                    'id' => $location->getCity()->getId(),
                    'zipcode' => $location->getCity()->getZipcode(),
                    'name' => $location->getCity()->getName(),
                ]
            ];
        }
        return $locationsList;
    }

}