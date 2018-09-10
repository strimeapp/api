<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\StatsBundle\Entity\Stats;

class CronNbAudiosCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:stats:nb-audios')
            ->setDescription('Get the number of registered audio files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Count the number of audio files
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_audio.id)' );
        $query->from( 'StrimeAPIAudioBundle:Audio','api_audio' );
        $nb_audios = $query->getQuery()->getSingleScalarResult();

        // Save the result in the stats table
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_audios'));

        if($stats == NULL) {
            $stats = new Stats;
            $stats->setName( 'number_of_audios' );
            $stats->setData( $nb_audios );
            $em->persist( $stats );
            $em->flush();
        }
        else {
            $stats->setData( $nb_audios );
            $em->persist( $stats );
            $em->flush();
        }

        $response = "[".date("Y-m-d H:i:s")."] OK: The data has been updated.";
        $output->writeln( $response );
    }
}
