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
            'title' => 'Invincible',
            'synopsis' => 'Synopsis : topo de la série',
            'category' => 'category_Action',
        ],
        [
            'title' => 'Avatar_TLAB',
            'synopsis' => 'Synopsis : topo de la série',
            'category' => 'category_Animation',
        ],
        [
            'title' => 'Spaced',
            'synopsis' => 'Synopsis : topo de la série',
            'category' => 'category_Humour',
        ]

    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::PROGRAMS as $programName) {
            $program = new Program();
            $program->setTitle($programName['title']);
            $program->setSynopsis($programName['synopsis']);
            $program->setCategory($this->getReference($programName['category']));
            $manager->persist($program);
            $this->addReference('program_' . $programName['title'], $program);
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
