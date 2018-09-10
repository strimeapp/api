<?php

namespace StrimeAPI\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use StrimeAPI\GlobalBundle\Token\TokenGenerator;

use StrimeAPI\UserBundle\Entity\Invoice;

class LoadInvoiceData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
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
        $invoice = new Invoice();
        $invoice->setInvoiceId("201611000001");
        $invoice->setStripeId("123abc");
        $invoice->setUser( $this->getReference('user') );
        $invoice->setTotalAmount(18.0);
        $invoice->setAmountWoTaxes(3.0);
        $invoice->setTaxes(3.0);
        $invoice->setTaxRate(20);
        $invoice->setDay(28);
        $invoice->setMonth(11);
        $invoice->setYear(2016);
        $invoice->setPlanStartDate("28/11/2016");
        $invoice->setPlanEndDate("28/12/2016");

        $manager->persist($invoice);
        $manager->flush();

        $this->addReference('invoice', $invoice);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}