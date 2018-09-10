<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;
use StrimeAPI\UserBundle\Entity\UserFacebook;

class CronNbUsersFacebookSigninCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:nb-users-facebook-signin')
            ->setDescription('Get the number of users using Facebook Signin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of users
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_user_facebook.id)' );
        $query->from( 'StrimeAPIUserBundle:UserFacebook','api_user_facebook' );
        $nb_users = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_facebook_signin'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'number_of_users_facebook_signin' );
            $stats->setData( $nb_users );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $nb_users );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}
