<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Enum\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $durations = [60, 90, 120, 150, 180, 210];
        $interval = new \DateInterval('PT12H');
        $locationList = $manager->getRepository(Location::class)->findAll();
        $userList = $manager->getRepository(User::class)->findAll();
        $event = new Event();
        $dateStart = new \DateTime('2024-01-02 14:00:00');
        $event->setName('Paintball')
            ->setDateStart($dateStart)
            ->setDuration(180)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(20)
            ->setEventInfo($faker->text)
            ->setState(State::TERMINEE)
            ->setLocation($locationList[array_rand($locationList)])
            ->addParticipate($userList[array_rand($userList)])
            ->addParticipate($userList[array_rand($userList)])
            ->addParticipate($userList[array_rand($userList)])
            ->setEventOrganizer($userList[array_rand($userList)]);
        $manager->persist($event);


        $event = new Event();
        $dateStart = new \DateTime('2024-04-14 14:00:00');
        $event->setName('Resto à gogo')
            ->setDateStart($dateStart)
            ->setDuration(90)
            ->setState(State::OUVERTE)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(8)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-01-03 11:00:00');
        $event->setName('Resto de la promo')
            ->setDateStart($dateStart)
            ->setDuration(90)
            ->setState(State::TERMINEE)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(8)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,8); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-03 14:00:00');
        $event->setName('Balade a vélo')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setState(State::OUVERTE)
            ->setMaxRegistration(8)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,7); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-05 18:00:00');
        $event->setName('Apéro cinéma')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setState(State::OUVERTE)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(8)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,7); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-12 08:00:00');
        $event->setName('Parc Asterix')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setState(State::OUVERTE)
            ->setMaxRegistration(15)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,14); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-03 17:00:00');
        $event->setName('Soirée jeux de société')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(8)
            ->setState(State::OUVERTE)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,7); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-03 14:00:00');
        $event->setName('Atelier cuisine')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setState(State::OUVERTE)
            ->setMaxRegistration(8)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,7); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-03-28 14:00:00');
        $event->setName('Escalade en salle')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setState(State::OUVERTE)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(12)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,11); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-07 14:00:00');
        $event->setName('Balade en Kayak')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setState(State::OUVERTE)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(12)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,11); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-07 12:00:00');
        $event->setName('Pique-nique gourmet dans un parc')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(20)
            ->setState(State::CLOTURE)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= 20; $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $event = new Event();
        $dateStart = new \DateTime('2024-04-05 09:00:00');
        $event->setName('Visite musée')
            ->setDateStart($dateStart)
            ->setDuration($durations[array_rand($durations)])
            ->setState(State::OUVERTE)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(25)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,24); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);

        $campus = (new Campus());
        $campus->setName('Campus en ligne');
        $manager->persist($campus);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class,LocationFixtures::class];
    }
}
