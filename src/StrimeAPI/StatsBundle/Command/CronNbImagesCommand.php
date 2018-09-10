<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;

class CronNbImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:nb-images')
            ->setDescription('Get the number of registered images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of videos
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_image.id)' );
        $query->from( 'StrimeAPIImageBundle:Image','api_image' );
        $nb_images = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_images'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'number_of_images' );
            $stats->setData( $nb_images );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $nb_images );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}
