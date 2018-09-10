<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;
use StrimeAPI\VideoBundle\Entity\Video;

class CronAverageVideoSizeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:average-video-size')
            ->setDescription('Get the average weight of videos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of users
        $query = $em->createQueryBuilder();
        $query->select( 'avg(api_video.size)' );
        $query->from( 'StrimeAPIVideoBundle:Video','api_video' );
        $average_video_size = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'average_video_size'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'average_video_size' );
            $stats->setData( $average_video_size );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $average_video_size );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}