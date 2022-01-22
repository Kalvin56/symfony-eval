<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Faker\Factory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 25; $i++)
        {
            $product = new Product();

            $name = $faker->word();
            $price = $faker->randomNumber(2);
            $description = $faker->text(400);
            $created_at = new \DateTime;
            $day = rand(1,20);
            $created_at->modify('-'.$day.' day');
            $product->setName($name)
                    ->setPrice($price)
                    ->setDescription($description)
                    ->setCreatedAt($created_at);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
