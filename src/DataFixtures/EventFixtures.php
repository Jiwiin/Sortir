<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $interval = new \DateInterval('PT12H');
        $locationList = $manager->getRepository(Location::class)->findAll();
        $userList = $manager->getRepository(User::class)->findAll();
        $event = new Event();
        $dateStart = \DateTimeImmutable::createFromMutable($faker->dateTime());

        $event->setName('Paintball')
            ->setDateStart($dateStart)
            ->setDuration(180)
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(20)
            ->setEventInfo($faker->text)
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
            ->setDateLimitRegistration($dateStart->sub($interval))
            ->setMaxRegistration(8)
            ->setEventInfo($faker->text)
            ->setLocation($locationList[array_rand($locationList)])
            ->setEventOrganizer($userList[array_rand($userList)]);

        for($i = 1; $i <= rand(3,8); $i++){
            $event->addParticipate($userList[array_rand($userList)]);
        }
        $manager->persist($event);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class,LocationFixtures::class];
    }
}
