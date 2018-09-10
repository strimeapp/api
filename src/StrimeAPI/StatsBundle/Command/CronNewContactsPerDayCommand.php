<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\NbContactsPerDay;

class CronNewContactsPerDayCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:new-contacts-per-day')
            ->setDescription('Get the number of contacts created every day since a certain date')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Set the dates
        $from_date = new \DateTime('yesterday');
        $from_date->setTime(0, 0, 0);

        $to_date = clone $from_date;
        $to_date->modify('+1 day');

        $yesterday = new \DateTime('yesterday');
        $yesterday->setTime(0, 0, 0);
        $yesterday_date_time = $to_date->format("U");

        // Count the number of contacts created
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_contact.id)' );
        $query->from( 'StrimeAPIUserBundle:Contact','api_contact' );
        $query->where('api_contact.created_at >= :from_date');
        $query->andWhere('api_contact.created_at < :to_date');
        $query->setParameter('from_date', $from_date);
        $query->setParameter('to_date', $to_date);
        $nb_contacts = $query->getQuery()->getSingleScalarResult();

        $date_time = $from_date->format("U");

        // Check if this record exists in the table
        // Save the result in the stats table
        $stats = new NbContactsPerDay;
        $stats = $em->getRepository('StrimeAPIStatsBundle:NbContactsPerDay')->findOneBy(array('date_time' => $date_time));

        if($stats == NULL) {

            // Get the stats of yesterday
            $query = $em->createQueryBuilder();
            $query->select( 'api_stats_nb_contacts.total_nb_contacts' );
            $query->from( 'StrimeAPIStatsBundle:NbContactsPerDay','api_stats_nb_contacts' );
            $query->orderBy('api_stats_nb_contacts.id', 'DESC');
            $query->setMaxResults(1);
            $stats_yesterday = $query->getQuery()->getResult();

            // Set the total of contacts
            $query = $em->createQueryBuilder();
            $query->select( 'count(api_contact.id)' );
            $query->from( 'StrimeAPIUserBundle:Contact','api_contact' );
            $total_nb_contacts = $query->getQuery()->getSingleScalarResult();

            // Save the data
            $stats = new NbContactsPerDay;
            $stats->setDateTime( $date_time );
            $stats->setNbContacts( $nb_contacts );
            $stats->setTotalNbContacts( $total_nb_contacts );
            $em->persist( $stats );
            $em->flush();

            // Send a message back to console
            $output->writeln( "[".date("Y-m-d H:i:s")."] ".$from_date->format("d/m/Y") .": ".$nb_contacts." contacts" );
        }
        else {

            // Send a message back to console
            $output->writeln( "[".date("Y-m-d H:i:s")."] ".$from_date->format("d/m/Y") .": there is already a record" );
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The data has been updated." );
    }
}
