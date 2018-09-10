<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;
use StrimeAPI\AudioBundle\Entity\Audio;

class CronAverageAudioSizeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:average-audio-size')
            ->setDescription('Get the average weight of audio files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of users
        $query = $em->createQueryBuilder();
        $query->select( 'avg(api_audio.size)' );
        $query->from( 'StrimeAPIAudioBundle:Audio','api_audio' );
        $average_audio_size = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'average_audio_size'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'average_audio_size' );
            $stats->setData( $average_audio_size );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $average_audio_size );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}
