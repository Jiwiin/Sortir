<?php

namespace App\DataFixtures;



use App\Entity\City;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cityList = $manager->getRepository(City::class)->findAll();


        $location = (new Location());
        $location->setName('Cinéma Pathé')
            ->setStreet('5 rue charles de gaulle')
            ->setCity($cityList[array_rand($cityList)]);
        $manager->persist($location);

        $location = (new Location());
        $location->setName('Restaurant Hippopotamus')
            ->setStreet('12 avenue mistral')
            ->setCity($cityList[array_rand($cityList)]);
        $manager->persist($location);

        $location = (new Location());
        $location->setName('Parc Adventure')
            ->setStreet('45 Chemin de Saint Pierre')
            ->setCity($cityList[array_rand($cityList)]);
        $manager->persist($location);

        $manager->flush();
    }
}
