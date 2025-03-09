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
        $category21 = new Category();

        $category11->setName('RAM');
        $category12->setName('CPUs (Processors)');
        $category13->setName('Drives');
        $category14->setName('Network cards');
        $category15->setName('Power supplies');
        $category21->setName('Rails');

        $category4->addChild($category11);
        $category4->addChild($category12);
        $category4->addChild($category13);
        $category4->addChild($category14);
        $category4->addChild($category15);
        $category4->addChild($category21);

        $manager->persist($category11);
        $manager->persist($category12);
        $manager->persist($category13);
        $manager->persist($category14);
        $manager->persist($category15);
        $manager->persist($category21);


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

        $r620 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/SERVERS/DELL/PE_R620_FRONT.jpg');
        $image2->setPath('images/SERVERS/DELL/PE_R620_BACK.jpg');
        $r620->addImage($image1);
        $r620->addImage($image2);
        $r620->setName('PowerEdge R620  ');
        $r620->setType('server');
        $r620->setCondition('used');
        $r620->setPrice(mt_rand(10, 100));
        $r620->setWeight(mt_rand(100, 1000));
        $r620->setAmount(mt_rand(1, 10));
        $r620->setBrand('Dell');
        $category17->addProduct($r620);
        $r620->addSpecification($specification1);
        $r620->addSpecification($specification3);
        $r620->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($r620);
        $manager->persist($image1);
        $manager->persist($image2);

        $r630 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/SERVERS/DELL/PE_R630_FRONT.jpg');
        $image2->setPath('images/SERVERS/DELL/PE_R630_BACK.jpg');
        $r630->addImage($image1);
        $r630->addImage($image2);
        $r630->setName('PowerEdge R630  ');
        $r630->setType('server');
        $r630->setCondition('used');
        $r630->setPrice(mt_rand(10, 100));
        $r630->setWeight(mt_rand(100, 1000));
        $r630->setAmount(mt_rand(1, 10));
        $r630->setBrand('Dell');
        $category17->addProduct($r630);
        $r630->addSpecification($specification1);
        $r630->addSpecification($specification3);
        $r630->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($r630);
        $manager->persist($image1);
        $manager->persist($image2);

        $r720 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/SERVERS/DELL/PE_R720_FRONT.jpg');
        $image2->setPath('images/SERVERS/DELL/PE_R720_BACK.jpg');
        $r720->addImage($image1);
        $r720->addImage($image2);
        $r720->setName('PowerEdge R720  ');
        $r720->setType('server');
        $r720->setCondition('used');
        $r720->setPrice(mt_rand(10, 100));
        $r720->setWeight(mt_rand(100, 1000));
        $r720->setAmount(mt_rand(1, 10));
        $r720->setBrand('Dell');
        $category18->addProduct($r720);
        $r720->addSpecification($specification1);
        $r720->addSpecification($specification4);
        $r720->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($r720);
        $manager->persist($image1);
        $manager->persist($image2);

        $r730 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/SERVERS/DELL/PE_R730_FRONT.jpg');
        $image2->setPath('images/SERVERS/DELL/PE_R730_BACK.jpg');
        $r730->addImage($image1);
        $r730->addImage($image2);
        $r730->setName('PowerEdge R730  ');
        $r730->setType('server');
        $r730->setCondition('used');
        $r730->setPrice(mt_rand(10, 100));
        $r730->setWeight(mt_rand(100, 1000));
        $r730->setAmount(mt_rand(1, 10));
        $r730->setBrand('Dell');
        $category18->addProduct($r730);
        $r730->addSpecification($specification1);
        $r730->addSpecification($specification4);
        $r730->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($r730);
        $manager->persist($image1);
        $manager->persist($image2);

        $dl360_25 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/SERVERS/HP/DL360_25_FRONT.jpg');
        $image2->setPath('images/SERVERS/HP/DL360_25_BACK.jpg');
        $dl360_25->addImage($image1);
        $dl360_25->addImage($image2);
        $dl360_25->setName('ProLiant DL360 Gen7 ');
        $dl360_25->setType('server');
        $dl360_25->setCondition('used');
        $dl360_25->setPrice(mt_rand(10, 100));
        $dl360_25->setWeight(mt_rand(100, 1000));
        $dl360_25->setAmount(mt_rand(1, 10));
        $dl360_25->setBrand('HP');
        $category19->addProduct($dl360_25);
        $dl360_25->addSpecification($specification1);
        $dl360_25->addSpecification($specification3);
        $dl360_25->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($dl360_25);
        $manager->persist($image1);
        $manager->persist($image2);

        $dl360_35 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/SERVERS/HP/DL360_35_FRONT.jpg');
        $image2->setPath('images/SERVERS/HP/DL360_35_BACK.jpg');
        $dl360_35->addImage($image1);
        $dl360_35->addImage($image2);
        $dl360_35->setName('ProLiant DL360 Gen7 ');
        $dl360_35->setType('server');
        $dl360_35->setCondition('used');
        $dl360_35->setPrice(mt_rand(10, 100));
        $dl360_35->setWeight(mt_rand(100, 1000));
        $dl360_35->setAmount(mt_rand(1, 10));
        $dl360_35->setBrand('HP');
        $category19->addProduct($dl360_35);
        $dl360_35->addSpecification($specification1);
        $dl360_35->addSpecification($specification3);
        $dl360_35->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($dl360_35);
        $manager->persist($image1);
        $manager->persist($image2);

        $dl160 = new Product();
        $image1 = new Image();
        $image1->setPath('images/SERVERS/HP/DL160_FRONT.jpg');
        $dl160->addImage($image1);
        $dl160->setName('ProLiant DL160 Gen7 ');
        $dl160->setType('server');
        $dl160->setCondition('used');
        $dl160->setPrice(mt_rand(10, 100));
        $dl160->setWeight(mt_rand(100, 1000));
        $dl160->setAmount(mt_rand(1, 10));
        $dl160->setBrand('HP');
        $category19->addProduct($dl160);
        $dl160->addSpecification($specification1);
        $dl160->addSpecification($specification3);
        $dl160->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($dl160);
        $manager->persist($image1);

        $asa5515 = new Product();
        $image1 = new Image();
        $image2 = new Image();
        $image1->setPath('images/NETWORK/CISCO/ASA5515_FRONT.jpg');
        $image2->setPath('images/SERVERS/HP/ASA5515_BACK.jpg');
        $asa5515->addImage($image1);
        $asa5515->addImage($image2);
        $asa5515->setName('Cisco ASA5515 ');
        $asa5515->setType('network equipment');
        $asa5515->setCondition('used');
        $asa5515->setPrice(mt_rand(10, 100));
        $asa5515->setWeight(mt_rand(100, 1000));
        $asa5515->setAmount(mt_rand(1, 10));
        $asa5515->setBrand('Cisco');
        $category10->addProduct($asa5515);
        $asa5515->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($asa5515);
        $manager->persist($image1);
        $manager->persist($image2);

        $rack = new Product();
        $image1 = new Image();
        $image1->setPath('images/OTHERS/RACKS/RACK_FRONT.jpg');
        $rack->addImage($image1);
        $rack->setName('Rack ');
        $rack->setType('other');
        $rack->setCondition('used');
        $rack->setPrice(mt_rand(10, 100));
        $rack->setWeight(mt_rand(100, 1000));
        $rack->setAmount(mt_rand(1, 10));
        $rack->setBrand('Rack');
        $rack->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $category16->addProduct($rack);
        $manager->persist($rack);
        $manager->persist($image1);

        $ram8g = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/RAM/RAM_FRONT.jpg');
        $ram8g->addImage($image1);
        $ram8g->setName('RAM 8Gb ');
        $ram8g->setType('component');
        $ram8g->setCondition('used');
        $ram8g->setPrice(mt_rand(10, 100));
        $ram8g->setWeight(mt_rand(100, 1000));
        $ram8g->setAmount(mt_rand(1, 10));
        $ram8g->setBrand('SK Hynix');
        $category11->addProduct($ram8g);
        $ram8g->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($ram8g);
        $manager->persist($image1);

        $ram16g = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/RAM/RAM_FRONT.jpg');
        $ram16g->addImage($image1);
        $ram16g->setName('RAM 16Gb ');
        $ram16g->setType('component');
        $ram16g->setCondition('used');
        $ram16g->setPrice(mt_rand(10, 100));
        $ram16g->setWeight(mt_rand(100, 1000));
        $ram16g->setAmount(mt_rand(1, 10));
        $ram16g->setBrand('HP');
        $category14->addProduct($ram16g);
        $ram16g->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($ram16g);
        $manager->persist($image1);

        $ram4g = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/RAM/RAM_FRONT.jpg');
        $ram4g->addImage($image1);
        $ram4g->setName('RAM 4Gb ');
        $ram4g->setType('component');
        $ram4g->setCondition('used');
        $ram4g->setPrice(mt_rand(10, 100));
        $ram4g->setWeight(mt_rand(100, 1000));
        $ram4g->setAmount(mt_rand(1, 10));
        $ram4g->setBrand('HP');
        $category14->addProduct($ram4g);
        $ram4g->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($ram4g);
        $manager->persist($image1);


        $dl380 = new Product();
        $image1 = new Image();
        $image1->setPath('images/STORAGES/HP/DL380_G7_FRONT.jpg');
        $dl380->addImage($image1);
        $dl380->setName('ProLiant HP DL380 G7 ');
        $dl380->setType('storage');
        $dl380->setCondition('used');
        $dl380->setPrice(mt_rand(10, 100));
        $dl380->setWeight(mt_rand(1000, 1500));
        $dl380->setAmount(mt_rand(1, 10));
        $dl380->setBrand('HP');
        $category19->addProduct($dl380);
        $dl380->addSpecification($specification1);
        $dl380->addSpecification($specification3);
        $dl380->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($dl380);
        $manager->persist($image1);

        $psu460 = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/PSU/460_HP.jpg');
        $psu460->addImage($image1);
        $psu460->setName('PSU 460W ');
        $psu460->setType('component');
        $psu460->setCondition('used');
        $psu460->setPrice(mt_rand(10, 100));
        $psu460->setWeight(mt_rand(100, 1000));
        $psu460->setAmount(mt_rand(1, 10));
        $psu460->setBrand('HP');
        $category15->addProduct($psu460);
        $psu460->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($psu460);
        $manager->persist($image1);

        $psu750 = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/PSU/750_HP.jpg');
        $psu750->addImage($image1);
        $psu750->setName('PSU 750W ');
        $psu750->setType('component');
        $psu750->setCondition('used');
        $psu750->setPrice(mt_rand(10, 100));
        $psu750->setWeight(mt_rand(100, 1000));
        $psu750->setAmount(mt_rand(1, 10));
        $psu750->setBrand('HP');
        $category15->addProduct($psu750);
        $psu750->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($psu750);
        $manager->persist($image1);

        $CPU = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/CPU/CPU.jpg');
        $CPU->addImage($image1);
        $CPU->setName('CPU ');
        $CPU->setType('component');
        $CPU->setCondition('used');
        $CPU->setPrice(mt_rand(10, 100));
        $CPU->setWeight(mt_rand(100, 1000));
        $CPU->setAmount(mt_rand(1, 10));
        $CPU->setBrand('Intel');
        $category12->addProduct($CPU);
        $CPU->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($CPU);
        $manager->persist($image1);

        $hdd1_2 = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/HDD/HDD.jpg');
        $hdd1_2->addImage($image1);
        $hdd1_2->setName('HDD ');
        $hdd1_2->setType('component');
        $hdd1_2->setCondition('used');
        $hdd1_2->setPrice(mt_rand(10, 100));
        $hdd1_2->setWeight(mt_rand(100, 1000));
        $hdd1_2->setAmount(mt_rand(1, 10));
        $hdd1_2->setBrand('Dell');
        $category13->addProduct($hdd1_2);
        $hdd1_2->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($hdd1_2);
        $manager->persist($image1);

        $sfpNetwork = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/CARDS/SFP.jpg');
        $sfpNetwork->addImage($image1);
        $sfpNetwork->setName('Network card ');
        $sfpNetwork->setType('component');
        $sfpNetwork->setCondition('used');
        $sfpNetwork->setPrice(mt_rand(10, 100));
        $sfpNetwork->setWeight(mt_rand(100, 1000));
        $sfpNetwork->setAmount(mt_rand(1, 10));
        $sfpNetwork->setBrand('Dell');
        $category14->addProduct($sfpNetwork);
        $sfpNetwork->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($sfpNetwork);
        $manager->persist($image1);

        $rail = new Product();
        $image1 = new Image();
        $image1->setPath('images/COMPONENTS/RAILS/RAILS.jpg');
        $rail->addImage($image1);
        $rail->setName('Rails kit');
        $rail->setType('component');
        $rail->setCondition('used');
        $rail->setPrice(mt_rand(10, 100));
        $rail->setWeight(mt_rand(100, 1000));
        $rail->setAmount(mt_rand(1, 10));
        $rail->setBrand('HP');
        $category21->addProduct($rail);
        $rail->setDescription('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam in lorem sit amet leo accumsan lacinia. Aliquam id dolor. Integer lacinia. Curabitur vitae diam non enim vestibulum interdum. Aliquam in lorem sit amet leo accumsan lacinia. Maecenas lorem. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Aenean fermentum risus id tortor. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Nullam dapibus fermentum ipsum. Etiam posuere lacus quis dolor. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. In enim a arcu imperdiet malesuada.

            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Quisque tincidunt scelerisque libero. Aliquam ante. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Duis risus. Integer lacinia. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Maecenas aliquet accumsan leo. Nullam eget nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Duis viverra diam non justo. Fusce tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Aliquam erat volutpat. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Fusce suscipit libero eget elit.');
        $manager->persist($rail);
        $manager->persist($image1);

        $dl360_25->addRelated($rail);
        $dl360_25->addRelated($psu460);
        $dl360_25->addRelated($CPU);
        $dl360_25->addRelated($psu750);

        $dl360_35->addRelated($rail);
        $dl360_35->addRelated($psu460);
        $dl360_35->addRelated($CPU);
        $dl360_35->addRelated($psu750);

        $dl160->addRelated($rail);
        $dl160->addRelated($psu460);
        $dl160->addRelated($CPU);
        $dl160->addRelated($psu750);

        $dl380->addRelated($rail);
        $dl380->addRelated($psu460);
        $dl380->addRelated($CPU);
        $dl380->addRelated($psu750);

        $r730->addRelated($ram8g);
        $r730->addRelated($hdd1_2);
        $r730->addRelated($CPU);
        $r730->addRelated($ram4g);

        $r720->addRelated($ram8g);
        $r720->addRelated($hdd1_2);
        $r720->addRelated($CPU);
        $r720->addRelated($ram4g);

        $r630->addRelated($ram8g);
        $r630->addRelated($hdd1_2);
        $r630->addRelated($CPU);
        $r630->addRelated($ram4g);

        $r620->addRelated($ram8g);
        $r620->addRelated($hdd1_2);
        $r620->addRelated($CPU);
        $r620->addRelated($ram4g);

        $manager->flush();
    }
}
