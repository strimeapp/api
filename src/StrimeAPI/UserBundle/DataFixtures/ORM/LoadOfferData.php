<?php

namespace StrimeAPI\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use StrimeAPI\UserBundle\Entity\Offer;

class LoadOfferData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
	/**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    public function load(ObjectManager $manager)
    {
        $offer = new Offer();
        $offer->setSecretId("ba7ce04669");
        $offer->setName('Gratuite');
        $offer->setPrice(0);
        $offer->setStorageAllowed(0.25);
        $offer->setNbVideos(2);
        $manager->persist($offer);
        $manager->flush();

        $this->addReference('offer', $offer);

        $offer = new Offer();
        $offer->setSecretId("937dacf8b2");
        $offer->setName('Pro');
        $offer->setPrice(15);
        $offer->setStorageAllowed(10);
        $offer->setNbVideos(0);
        $manager->persist($offer);
        $manager->flush();

        $offer = new Offer();
        $offer->setSecretId("b59b806f3e");
        $offer->setName('Expert');
        $offer->setPrice(29);
        $offer->setStorageAllowed(50);
        $offer->setNbVideos(0);
        $manager->persist($offer);
        $manager->flush();

        $offer = new Offer();
        $offer->setSecretId("15bf359903");
        $offer->setName('Advanced');
        $offer->setPrice(0);
        $offer->setStorageAllowed(100);
        $offer->setNbVideos(0);
        $manager->persist($offer);
        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}