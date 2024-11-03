<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    const PROGRAMS = [
        [
            'title' => 'Titre 1',
            'synopsis' => 'some texte',
            'category' => 'category_Action',
        ],
        [
            'title' => 'Titre 2',
            'synopsis' => 'some texte',
            'category' => 'category_Action',
        ],
        [
            'title' => 'Titre 3',
            'synopsis' => 'some texte',
            'category' => 'category_Action',
        ],
        [
            'title' => 'Titre 4',
            'synopsis' => 'some texte',
            'category' => 'category_Action',
        ],
        [
            'title' => 'Titre 5',
            'synopsis' => 'some texte',
            'category' => 'category_Horreur',
        ]

    ];

    public function load(ObjectManager $manager)
    {
        foreach(self::PROGRAMS as $programName)  {  
            $program = new Program();
            $program->setTitle($programName['title']);
            $program->setSynopsis($programName['synopsis']);
            $program->setCategory($this->getReference($programName['category']));
            $manager->persist($program);
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
