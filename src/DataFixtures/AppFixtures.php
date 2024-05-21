<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Player;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create();

          // création d'un admin
          $admin = new User();
          $admin->setFirstName('Jordan')
              ->setLastName('Berti')
              ->setEmail('berti@epse.be')
              ->setPassword($this->passwordHasher->hashPassword($admin, 'password'))
              ->setRoles(['ROLE_ADMIN']);
          $manager->persist($admin);

        for($t=1; $t<=10; $t++)
        {
            $team = new Team();
            $team->setName($faker->name())
                ->setLogo("logo.svg");

            $manager->persist($team);

            for($p=1; $p<=10; $p++)
            {
                $player = new Player();
                $player->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName())
                    ->setBirthday($faker->dateTimeBetween("-25 years"))
                    ->setNumber(rand(1,99))
                    ->setPicture("michael-jordan-looks.jpg")
                    ->setTeam($team);

                $manager->persist($player);
            }

        }

        $manager->flush();
    }
}
