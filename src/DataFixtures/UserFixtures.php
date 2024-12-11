<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        //config contributor
        $contributor = new User();
        $contributor->setPseudo('Contributor');
        $contributor->setEmail('contributor@mail.com');
        $contributor->setRoles(['ROLE_CONTRIBUTOR']);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $contributor,
            'pswd'
        );
        $contributor->setPassword($hashedPassword);
        $manager->persist($contributor);
        $this->addReference('user_1', $contributor);
        
        //config admin
        $admin = new User();
        $admin->setPseudo('Admin');
        $admin->setEmail('admin@mail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'adminpswd'
        );
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);
        $this->addReference('user_2', $admin);

        //injection en bdd des users
        $manager->flush();
    }
}
