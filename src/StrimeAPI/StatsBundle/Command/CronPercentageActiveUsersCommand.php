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
use StrimeAPI\StatsBundle\Entity\PercentageActiveUsersPerDay;

class CronPercentageActiveUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:percentage-active-users')
            ->setDescription('Get the percentage of active users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Set the date to compare to the last login
        $date_limit_active = strtotime('-30 days');
        $output->writeln( "[".date("Y-m-d H:i:s")."] 30 days ago: ".$date_limit_active );

        // Get the number of users
        $nb_users = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users'));
        $nb_users = $nb_users->getData();
        $output->writeln( "[".date("Y-m-d H:i:s")."] Nb users: ".$nb_users );

        // Count the number of active users
        // Active means that the last login happened less than 30 days ago.
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_user.id)' );
        $query->from( 'StrimeAPIUserBundle:User','api_user' );
        $query->where('api_user.last_login >= :date_limit_active');
        $query->setParameter('date_limit_active', $date_limit_active);
        $nb_active_users = $query->getQuery()->getSingleScalarResult();
        $output->writeln( "[".date("Y-m-d H:i:s")."] Nb active users: ".$nb_active_users );

        // Get the percentage of active users
        $percentage_active_users = round( ($nb_active_users / $nb_users) * 100, 2 );
        $output->writeln( "[".date("Y-m-d H:i:s")."] % of active users: ".$percentage_active_users );

        // Save the result in the stats table
        $from_date = new \DateTime('yesterday');
        $from_date->setTime(0, 0, 0);
        $date_time = $from_date->format("U");
        $stats = $em->getRepository('StrimeAPIStatsBundle:PercentageActiveUsersPerDay')->findOneBy(array('date_time' => $date_time));

        if($stats == NULL) {
            $stats = new PercentageActiveUsersPerDay;
            $stats->setDateTime( $date_time );
            $stats->setPercentageActiveUsers( $percentage_active_users );
            $em->persist( $stats );
            $em->flush();

            // Send a message back to console
            $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The data has been updated." );
        }
        else {
            
            // Send a message back to console
            $output->writeln( "[".date("Y-m-d H:i:s")."] ".$from_date->format("d/m/Y") .": there is already a record" );
        }
    }
}