<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    //nombre d'objet user
    public const NUMBER_USER = 2;
    
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // L'objectif est de créer 10 séries qui appartiendront à une catégorie au hasard
        for($i = 1; $i <= 10; $i++) {
            $program = new Program();
            $program->setTitle($faker->words(3, true));
            $program->setSynopsis($faker->paragraphs(2, true));
            $program->setCategory($this->getReference('category_' . $faker->numberBetween(1, 5)));
            $program->setOwner($this->getReference('user_' . $faker->numberBetween(1, self::NUMBER_USER)));
            

            $program->setSlug($this->slugger->slug($program->getTitle()));

            $manager->persist($program);
            $this->addReference('program_' . $i, $program);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            CategoryFixtures::class,
        ];
    }
}
