<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\MainCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $category1 = new Category();
        $category2 = new Category();
        $category3 = new Category();
        $category4 = new Category();
        $category5 = new Category();
        $category1->setName('Servers');
        $category2->setName('Storage');
        $category3->setName('Network Equipment');
        $category4->setName('Components');
        $category5->setName('Others');
        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->persist($category4);
        $manager->persist($category5);

        $category6 = new Category();
        $category7 = new Category();
        $category8 = new Category();
        $category9 = new Category();
        $category10 = new Category();
        $category6->setName('Dell');
        $category7->setName('Hp');
        $category1->addChild($category6);
        $category1->addChild($category7);
        $category8->setName('Dell');
        $category9->setName('Hp');
        $category2->addChild($category8);
        $category2->addChild($category9);
        $category10->setName('Switches');
        $category3->addChild($category10);
        $manager->persist($category6);
        $manager->persist($category7);
        $manager->persist($category8);
        $manager->persist($category9);
        $manager->persist($category10);


        $category11 = new Category();
        $category12 = new Category();
        $category13 = new Category();
        $category14 = new Category();
        $category15 = new Category();

        $category11->setName('RAM');
        $category12->setName('CPUs (Processors)');
        $category13->setName('Drives');
        $category14->setName('Network cards');
        $category15->setName('Power supplies');

        $category4->addChild($category11);
        $category4->addChild($category12);
        $category4->addChild($category13);
        $category4->addChild($category14);
        $category4->addChild($category15);

        $manager->persist($category11);
        $manager->persist($category12);
        $manager->persist($category13);
        $manager->persist($category14);
        $manager->persist($category15);


        $category16 = new Category();
        $category16->setName('Racks');
        $category5->addChild($category16);
        $manager->persist($category16);
        $category17 = new Category();
        $category18 = new Category();
        $category19 = new Category();
        $category20 = new Category();
        $category17->setName('2.5 FormFactor');
        $category18->setName('3.5 FormFactor');
        $category19->setName('2.5 FormFactor');
        $category20->setName('3.5 FormFactor');

        $category6->addChild($category17);
        $category6->addChild($category18);
        $category7->addChild($category19);
        $category7->addChild($category20);

        $manager->persist($category17);
        $manager->persist($category18);
        $manager->persist($category19);
        $manager->persist($category20);

        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category17->addProduct($product);
            $manager->persist($product);
        }

        for ($i = 10; $i < 20; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category18->addProduct($product);
            $manager->persist($product);
        }

        for ($i = 20; $i < 30; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category19->addProduct($product);
            $manager->persist($product);
        }

        for ($i = 30; $i < 40; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category10->addProduct($product);
            $manager->persist($product);
        }

        for ($i = 40; $i < 50; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category16->addProduct($product);
            $manager->persist($product);
        }

        for ($i = 50; $i < 60; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category11->addProduct($product);
            $manager->persist($product);
        }

        for ($i = 50; $i < 60; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $category14->addProduct($product);
            $manager->persist($product);
        }


        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
