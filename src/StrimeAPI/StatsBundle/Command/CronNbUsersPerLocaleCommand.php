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

class CronNbUsersPerLocaleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:nb-users-per-locale')
            ->setDescription('Get the number of users per locale')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Write out in the terminal that we are beginning the script
        $output->writeln( "[".date("Y-m-d H:i:s")."] Collecting the number of users per locale" );

        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Set the results
        $results = array();

        // Count the number of users who have chosen to be in french
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_user.id)' );
        $query->from( 'StrimeAPIUserBundle:User','api_user' );
        $query->where('api_user.locale = :locale');
        $query->setParameter('locale', 'fr');
        $nb_users = $query->getQuery()->getSingleScalarResult();

        $results[] = array(
            "locale_id" => 'fr',
            "locale_name" => 'French',
            "nb_users" => $nb_users
        );

        // Write out in the terminal the result for this locale
        $output->writeln( "[".date("Y-m-d H:i:s")."] French: ".$nb_users );

        // Count the number of users who have chosen to be in english
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_user.id)' );
        $query->from( 'StrimeAPIUserBundle:User','api_user' );
        $query->where('api_user.locale = :locale');
        $query->setParameter('locale', 'en');
        $nb_users = $query->getQuery()->getSingleScalarResult();

        $results[] = array(
            "locale_id" => 'en',
            "locale_name" => 'English',
            "nb_users" => $nb_users
        );

        // Write out in the terminal the result for this locale
        $output->writeln( "[".date("Y-m-d H:i:s")."] English: ".$nb_users );

        // Count the number of users who have chosen to be in spanish
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_user.id)' );
        $query->from( 'StrimeAPIUserBundle:User','api_user' );
        $query->where('api_user.locale = :locale');
        $query->setParameter('locale', 'es');
        $nb_users = $query->getQuery()->getSingleScalarResult();

        $results[] = array(
            "locale_id" => 'es',
            "locale_name" => 'Spanish',
            "nb_users" => $nb_users
        );

        // Write out in the terminal the result for this locale
        $output->writeln( "[".date("Y-m-d H:i:s")."] Spanish: ".$nb_users );

        // Encode in JSON the results to save them in the database
        $results = json_encode($results);

        // Save the results in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_per_locale'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'number_of_users_per_locale' );
            $stats->setDataJson( $results );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setDataJson( $results );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}
