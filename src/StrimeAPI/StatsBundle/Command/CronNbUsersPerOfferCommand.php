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

class CronNbUsersPerOfferCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:nb-users-per-offer')
            ->setDescription('Get the number of users per offer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Write out in the terminal that we are beginning the script
        $output->writeln( "[".date("Y-m-d H:i:s")."] Collecting the number of users per offer" );

        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the offers
        $offers = $em->getRepository('StrimeAPIUserBundle:Offer')->findAll();

        // Set the results
        $results = array();

        // Foreach offer
        if($offers != NULL) {
            foreach ($offers as $offer) {
                
                // Count the number of users who have subscribed to this offer
                $query = $em->createQueryBuilder();
                $query->select( 'count(api_user.id)' );
                $query->from( 'StrimeAPIUserBundle:User','api_user' );
                $query->where('api_user.offer = :offer_id');
                $query->setParameter('offer_id', $offer->getId());
                $nb_users = $query->getQuery()->getSingleScalarResult();

                $results[] = array(
                    "offer_id" => $offer->getId(),
                    "offer_name" => $offer->getName(),
                    "nb_users" => $nb_users
                );

                // Write out in the terminal the result for this offer
                $output->writeln( "[".date("Y-m-d H:i:s")."] Offer #".$offer->getId().": ".$nb_users );
            }
        }

        // Encode in JSON the results to save them in the database
        $results = json_encode($results);

        // Save the results in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_per_offer'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'number_of_users_per_offer' );
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