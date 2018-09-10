<?php

namespace StrimeAPI\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\UserBundle\Entity\EmailToConfirm;
use StrimeAPI\UserBundle\Mailchimp\MailchimpManager;

class CronUpdateUsersStatusInMailchimpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:users:update-users-status-in-mailchimp')
            ->setDescription('Script which updates the status of the user in Mailchimp.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] Start the CRON job to update the status of the users in Mailchimp." );

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the entity manager." );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get all the users.
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get all the users" );
        $users = $em->getRepository('StrimeAPIUserBundle:User')->findAll();

        // Create Mailchimp Manager
        $mailchimp_manager = new MailchimpManager();
        $lists = json_decode( $mailchimp_manager->getLists() );

        // For each email to confirm
        foreach ($users as $user) {

            $output->writeln( "[".date("Y-m-d H:i:s")."] User ID: ".$user->getSecretId() );

            // We loop through the lists to make sure that the list of the clients exists.
            foreach ($lists->{"lists"} as $list) {

                if(strcmp($list->{"id"}, $this->getContainer()->getParameter('mailchimp_clients_list')) == 0) {

                    // Set the mailchimp parameters
                    $mailchimp_manager->email = $user->getEmail();
                    $mailchimp_manager->list = $this->getContainer()->getParameter('mailchimp_clients_list');
                    $mailchimp_member = $mailchimp_manager->getMember();
                    $mailchimp_member = json_decode( $mailchimp_member );
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Mailchimmp status: ".$mailchimp_member->{'status'} );

                    // If the user exists in Mailchimp, edit his profile
                    if($mailchimp_member->{'status'} != 404) {
                        $mailchimp_manager->old_email = $user->getEmail();
                        $mailchimp_manager->active = $user->getStatus();

                        $subscription = $mailchimp_manager->editMember();
                        $output->writeln( "[".date("Y-m-d H:i:s")."] User ID: ". var_export($subscription, TRUE) );
                    }
                }
            }
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] End of the script" );
    }
}
