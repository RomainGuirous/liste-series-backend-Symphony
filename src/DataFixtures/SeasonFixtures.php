<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // L'objectif est de créer 5 saisons pour les 10 séries => 50 saisons
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                $season = new Season();
                $season->setProgram($this->getReference('program_' . $i));
                $season->setNumber($j);
                $season->setYear($faker->year());
                $season->setDescription($faker->sentence());

                $manager->persist($season);
                $this->addReference('season' . $j . '_program' . $i, $season);
            }
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
