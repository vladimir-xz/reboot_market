<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Specification;
use App\Entity\Image;
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
        $category2->setName('Storages');
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
        $category7->setName('HP');
        $category1->addChild($category6);
        $category1->addChild($category7);
        $category8->setName('Dell');
        $category9->setName('HP');
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

        $specification1 = new Specification();
        $specification1->setProperty('Form-factor');
        $specification1->setValue('2.5 inch');
        $specification2 = new Specification();
        $specification2->setProperty('Form-factor');
        $specification2->setValue('3.5 inch');
        $specification3 = new Specification();
        $specification3->setProperty('Height');
        $specification3->setValue('1U');
        $specification4 = new Specification();
        $specification4->setProperty('Height');
        $specification4->setValue('2U');

        $manager->persist($specification1);
        $manager->persist($specification2);
        $manager->persist($specification3);
        $manager->persist($specification4);

        for ($i = 0; $i < 5; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image2 = new Image();
            $image1->setPath('images/SERVERS/DELL/PE_R630_FRONT.jpg');
            $image2->setPath('images/SERVERS/DELL/PE_R630_BACK.jpg');
            $product->addImage($image1);
            $product->addImage($image2);
            $product->setName('PowerEdge R630  ' . $i);
            $product->setType('server');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('Dell');
            $category17->addProduct($product);
            $product->addSpecification($specification1);
            $product->addSpecification($specification3);
            $manager->persist($product);
            $manager->persist($image1);
            $manager->persist($image2);
        }

        for ($i = 10; $i < 15; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image2 = new Image();
            $image1->setPath('images/SERVERS/DELL/PE_R730_FRONT.jpg');
            $image2->setPath('images/SERVERS/DELL/PE_R730_BACK.jpg');
            $product->addImage($image1);
            $product->addImage($image2);
            $product->setName('PowerEdge R730  ' . $i);
            $product->setType('server');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('Dell');
            $category18->addProduct($product);
            $product->addSpecification($specification2);
            $product->addSpecification($specification3);
            $manager->persist($product);
            $manager->persist($image1);
            $manager->persist($image2);
        }

        for ($i = 20; $i < 25; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image2 = new Image();
            $image1->setPath('images/SERVERS/HP/DL360_FRONT.jpg');
            $image2->setPath('images/SERVERS/HP/DL360_BACK.jpg');
            $product->addImage($image1);
            $product->addImage($image2);
            $product->setName('DL360 Gen7 ' . $i);
            $product->setType('server');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('HP');
            $category19->addProduct($product);
            $product->addSpecification($specification1);
            $product->addSpecification($specification3);
            $manager->persist($product);
            $manager->persist($image1);
            $manager->persist($image2);
        }

        for ($i = 30; $i < 35; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image2 = new Image();
            $image1->setPath('images/NETWORK/CISCO/ASA5515_FRONT.jpg');
            $image2->setPath('images/SERVERS/HP/ASA5515_BACK.jpg');
            $product->addImage($image1);
            $product->addImage($image2);
            $product->setName('Cisco ASA5515 ' . $i);
            $product->setType('network equipment');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('Cisco');
            $category10->addProduct($product);
            $manager->persist($product);
            $manager->persist($image1);
            $manager->persist($image2);
        }

        for ($i = 40; $i < 42; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image1->setPath('images/OTHERS/RACKS/RACK_FRONT.jpg');
            $product->addImage($image1);
            $product->setName('product ' . $i);
            $product->setType('other');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('Rack');
            $category16->addProduct($product);
            $manager->persist($product);
            $manager->persist($image1);
        }

        for ($i = 50; $i < 60; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image1->setPath('images/COMPONENTS/RAM_FRONT.jpg');
            $product->addImage($image1);
            $product->setName('RAM 8gb ' . $i);
            $product->setType('component');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('SK Hynix');
            $category11->addProduct($product);
            $manager->persist($product);
            $manager->persist($image1);
        }

        for ($i = 60; $i < 70; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image1->setPath('images/COMPONENTS/RAM_FRONT.jpg');
            $product->addImage($image1);
            $product->setName('RAM 16gb ' . $i);
            $product->setType('component');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(100, 1000));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('HP');
            $category14->addProduct($product);
            $manager->persist($product);
            $manager->persist($image1);
        }

        for ($i = 70; $i < 80; $i++) {
            $product = new Product();
            $image1 = new Image();
            $image1->setPath('images/STORAGES/HP/DL380_G7_FRONT.jpg');
            $product->addImage($image1);
            $product->setName('HP DL380 G7 ' . $i);
            $product->setType('storage');
            $product->setCondition('used');
            $product->setPrice(mt_rand(10, 100));
            $product->setWeight(mt_rand(1000, 1500));
            $product->setAmount(mt_rand(1, 10));
            $product->setBrand('HP');
            $category9->addProduct($product);
            $product->addSpecification($specification1);
            $product->addSpecification($specification4);
            $manager->persist($product);
            $manager->persist($image1);
        }
        $manager->flush();
    }
}
