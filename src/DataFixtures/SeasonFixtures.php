<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    const SEASONS = [
        [
            'number' => '1',
            'year' => '2021',
            'description' => 'Description : topo de la saison',
            'program' => 'Invincible'
        ],
        [
            'number' => '2',
            'year' => '2023',
            'description' => 'Description : topo de la saison',
            'program' => 'Invincible'
        ],
        [
            'number' => '1',
            'year' => '2005',
            'description' => 'Description : topo de la saison',
            'program' => 'Avatar_TLAB'
        ],
        [
            'number' => '2',
            'year' => '2006',
            'description' => 'Description : topo de la saison',
            'program' => 'Avatar_TLAB'
        ],
        [
            'number' => '3',
            'year' => '2007',
            'description' => 'Description : topo de la saison',
            'program' => 'Avatar_TLAB'
        ],
        [
            'number' => '1',
            'year' => '1999',
            'description' => 'Description : topo de la saison',
            'program' => 'Spaced'
        ],
        [
            'number' => '2',
            'year' => '2001',
            'description' => 'Description : topo de la saison',
            'program' => 'Spaced'
        ]

    ];

    public function load(ObjectManager $manager)
    {
        foreach(self::SEASONS as $seasonName)  {  
            $season = new Season();
            $season->setNumber($seasonName['number']);
            $season->setYear($seasonName['year']);
            $season->setDescription($seasonName['description']);
            $season->setProgram($this->getReference('program_' . $seasonName['program']));
            $manager->persist($season);
            $this->addReference('season' . $seasonName['number'] . '_' . $seasonName['program'], $season);
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
