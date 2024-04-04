<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct (
        private readonly UserPasswordHasherInterface $hasher
    ){

    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $campusList = $manager->getRepository(Campus::class)->findAll();

        $user = (new User());
        $user->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@admin.fr')
            ->setUsername('admin')
            ->setLastname("admin")
            ->setPhone("06".rand(00000001, 99999999))
            ->setStatus(true)
            ->setProfilePicture('profil.jpg')
            ->setFirstname("nous")
            ->setCampus($campusList[array_rand($campusList)])
            ->setPassword($this->hasher->hashPassword($user, 'azerty'));
        $manager->persist($user);


        for($i = 1; $i <= 30; $i++){
            $randomUser = new User();
            $randomUser->setRoles(['ROLE_USER'])
                ->setLastname($faker->lastname())
                ->setFirstname($faker->firstname())
                ->setPhone("06".rand(00000001, 99999999))
                ->setStatus(true)
                ->setCampus($campusList[array_rand($campusList)])
                ->setPassword($this->hasher->hashPassword($randomUser, 'azerty'));
            //Suppression des accents pour l'adresse mail et le pseudo
            $mail = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($randomUser->getFirstname() . '.' .$randomUser->getLastname() . "@sortir-eni.com");
            $pseudo = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($randomUser->getFirstname().rand(000001, 999999));
            $randomUser->setUsername(strtolower($pseudo));
            $randomUser->setEmail(strtolower($mail));

            $manager->persist($randomUser);
        }

        $manager->flush();
    }
}
