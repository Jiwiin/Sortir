<?php

namespace App\DataFixtures;


use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $city = (new City());
        $city->setName('Nantes');
        $city->setZipcode('44000');
        $manager->persist($city);
        $city = (new City());
        $city->setName('Rennes');
        $city->setZipcode('35000');
        $manager->persist($city);
        $city = (new City());
        $city->setName('Niort');
        $city->setZipcode('79000');
        $manager->persist($city);
        $city = (new City());
        $city->setName('Chessy');
        $city->setZipcode('77700');
        $manager->persist($city);

        $manager->flush();
    }
}
