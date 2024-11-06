<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // L'objectif est de créer 10 épisodes par saisons (50) => 500 épisodes
        for ($j = 1; $j <= 5; $j++) {
            for ($i = 1; $i <= 10; $i++) {
                $season = 'season' . $j . '_program' . $i;
                for ($k = 1; $k <= 10; $k++) {
                    $episode = new Episode();
                    $episode->setSeason($this->getReference($season));
                    $episode->setTitle($faker->sentence(3));
                    $episode->setSynopsis($faker->sentence());
                    $episode->setNumber($k);

                    $manager->persist($episode);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            SeasonFixtures::class,
        ];
    }
}
