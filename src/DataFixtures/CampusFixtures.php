<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campus = (new Campus());
        $campus->setName('Nantes');
        $manager->persist($campus);
        $campus = (new Campus());
        $campus->setName('Rennes');
        $manager->persist($campus);
        $campus = (new Campus());
        $campus->setName('Niort');
        $manager->persist($campus);

        $manager->flush();
    }
}
