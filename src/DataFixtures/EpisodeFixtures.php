<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    
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
                    $episode->setDuration($faker->numberBetween(20,60));
                    $episode->setSlug($this->slugger->slug($episode->getTitle()));

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
