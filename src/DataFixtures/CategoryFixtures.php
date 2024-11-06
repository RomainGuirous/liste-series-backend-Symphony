<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Puis ici nous demandons à la Factory de nous fournir un Faker
        $faker = Factory::create();

        /**
        * L'objet $faker que tu récupères est l'outil qui va te permettre 
        * de te générer toutes les données que tu souhaites
        */
        for($i = 1; $i <= 5; $i++) {
            $category = new Category();
            //Ce Faker va nous permettre d'alimenter l'instance de Category que l'on souhaite ajouter en base
            $category->setName($faker->word());

            $manager->persist($category);
            // Ici, on crée une référence à l'objet $category sous la forme 'category_1', 'category_2'...
            $this->addReference('category_' . $i, $category);
        }

        $manager->flush();
    }
}
