<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;
use StrimeAPI\UserBundle\Entity\User;

class CronNbContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:nb-contacts')
            ->setDescription('Get the number of registered contacts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of videos
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_contact.id)' );
        $query->from( 'StrimeAPIUserBundle:Contact','api_contact' );
        $nb_contacts = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_contacts'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'number_of_contacts' );
            $stats->setData( $nb_contacts );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $nb_contacts );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}