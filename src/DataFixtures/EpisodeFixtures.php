<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    const EPISODES = [
        [
            'title' => 'Episode ',
            'synopsis' => 'some texte',
            'number' => '8',
            'season' => '2',
            'program' => 'Invincible'
        ],
        [
            'title' => 'Episode ',
            'synopsis' => 'some texte',
            'number' => '20',
            'season' => '3',
            'program' => 'Avatar_TLAB'
        ],
        [
            'title' => 'Episode ',
            'synopsis' => 'some texte',
            'number' => '7',
            'season' => '2',
            'program' => 'Spaced'
        ],

    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::EPISODES as $episodeName) {
            for ($i = 1; $i <= $episodeName['season']; $i++) {
                for ($j = 1; $j <= $episodeName['number']; $j++) {
                    $episode = new Episode();
                    $episode->setTitle($episodeName['title'] . '_' . $j);
                    $episode->setSynopsis($episodeName['synopsis']);
                    $episode->setNumber($j);
                    $episode->setSeason($this->getReference('season' . $i . '_' . $episodeName['program']));
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
