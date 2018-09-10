<?php

namespace StrimeAPI\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\User;

class CronSetDefaultMailNotificationSettingsForUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:users:set-default-mail-notification-settings')
            ->setDescription('Script which sets all the users without a notification setting value empty to "now".')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] Start the CRON job to set the users notification setting." );

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the entity manager." );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get all the users with an empty notification field
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get all the users with an empty mail_notification field" );
        $users = $em->getRepository('StrimeAPIUserBundle:User')->findBy(array("mail_notification" => ""));

        // For each email to confirm
        foreach ($users as $user) {

            $output->writeln( "[".date("Y-m-d H:i:s")."] User ID: ".$user->getSecretId() );

            // Set this value to "now"
            $user->setMailNotification("now");
            $em->persist($user);
            $em->flush();
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] End of the script" );
    }
}
