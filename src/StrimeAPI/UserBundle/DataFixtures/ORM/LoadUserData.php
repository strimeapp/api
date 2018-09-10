<?php

namespace StrimeAPI\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use StrimeAPI\GlobalBundle\Token\TokenGenerator;

use StrimeAPI\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $user = new User();
        $user->setSecretId("68c8213a81");
        $user->setStripeId("123abc");
        $user->setFirstName('Romain');
        $user->setLastName('Biard');
        $user->setEmail('romain@digitallift.fr');
        $user->setOffer( $this->getReference('offer') );
        $user->setRole('admin');
        
        // We encrypt the password
        $password = "test";
        $password_time = time();
        $password = hash('sha512', $password_time.$password.$this->container->getParameter('secret'));

        $user->setPassword($password);
        $user->setPasswordTime($password_time);

        $manager->persist($user);
        $manager->flush();

        $this->addReference('user', $user);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}