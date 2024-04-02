<?php

namespace App\DataFixtures;



use App\Entity\City;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


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

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 30; $i++) {
            $Location = new Location();
            $Location->setName($faker->randomElement(['Cinéma', 'Parc', 'Musée', 'Galerie', 'Restaurant', 'Théatre', 'Stade', 'Centre commercial']) . " " .$faker->lastName);
            $Location->setStreet($faker->streetAddress);
            $Location->setLatitude($faker->latitude);
            $Location->setLongitude($faker->longitude);
            $Location->setCity($cityList[array_rand($cityList)]);

            $this->locations[] = $Location;
            $manager->persist($Location);
        }


        $manager->flush();
    }
}
