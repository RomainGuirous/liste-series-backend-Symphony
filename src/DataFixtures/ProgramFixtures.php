<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Program();
        $product ->setTitle('Un titre');
        $product ->setSynopsis('Un synopsis');
        $manager->persist($product);
        $manager->flush();
    }
}
