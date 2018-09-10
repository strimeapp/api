<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;
use StrimeAPI\ImageBundle\Entity\Image;

class CronAverageImageSizeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:average-image-size')
            ->setDescription('Get the average weight of images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of users
        $query = $em->createQueryBuilder();
        $query->select( 'avg(api_image.size)' );
        $query->from( 'StrimeAPIImageBundle:Image','api_image' );
        $average_image_size = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'average_image_size'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'average_image_size' );
            $stats->setData( $average_image_size );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $average_image_size );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}
