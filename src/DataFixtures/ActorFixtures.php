<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        
        for($i = 1; $i <= 10; $i++) {
            $actor = new Actor();
            $actor->setName($faker->name(2, true));
            $actor->addProgram($this->getReference('program_' .$faker->unique(true)->numberBetween(1, 10)));
            for($j = 1; $j <= 2; $j++) {
                $actor->addProgram($this->getReference('program_' .$faker->unique()->numberBetween(1, 10)));
            }
            $manager->persist($actor);
            $this->addReference('actor' . $i, $actor);
        }

        $manager->flush();
    }


    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ProgramFixtures::class,
        ];
    }
}
