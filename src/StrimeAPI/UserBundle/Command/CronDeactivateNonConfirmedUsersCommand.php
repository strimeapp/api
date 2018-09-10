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

class CronDeactivateNonConfirmedUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:users:deactivate-non-confirmed-users')
            ->setDescription('Script which deactivates all the non-confirmed users.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] Start the CRON job to deactivate the users that have not confirm their email address." );

        // Get the number of days after which we deactivate the users
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the number of days after which we deactivate the users." );
        $user_days_to_confirm_email = $this->getContainer()->getParameter('user_days_to_confirm_email');
        $output->writeln( "[".date("Y-m-d H:i:s")."] => " . $user_days_to_confirm_email );
        $now = new \DateTime();

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the entity manager." );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the webhook parameters
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the webhook parameters." );
        $strime_app_url = $this->getContainer()->getParameter('strime_app_url');
        $strime_app_token = $this->getContainer()->getParameter('strime_app_token');

        // Set the headers
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set the headers." );
        $headers_app = array(
            'Accept' => 'application/json',
            'X-Auth-Token' => $strime_app_token,
            'Content-type' => 'application/json'
        );

        // Set nginx auth
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set nginx auth." );
        $nginx_auth = NULL;
        if(strcmp( $this->getContainer()->get( 'kernel' )->getEnvironment(), "test" ) == 0) {
            $strime_app_nginx_username = $this->getContainer()->getParameter('strime_app_nginx_username');
            $strime_app_nginx_pwd = $this->getContainer()->getParameter('strime_app_nginx_pwd');
            $nginx_auth = [$strime_app_nginx_username, $strime_app_nginx_pwd];
        }

        // Get all the users that need to confirm their email address.
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get all the users that need to confirm their email address." );
        $emails_to_confirm = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findAll();

        // For each email to confirm
        foreach ($emails_to_confirm as $email_to_confirm) {

            $output->writeln( "[".date("Y-m-d H:i:s")."] User ID: ".$email_to_confirm->getUser()->getSecretId() );

            // Get the interval of days before the end of the confirmation period
            $token_creation_date = $email_to_confirm->getCreatedAt();
            $interval = $token_creation_date->diff($now);
            $last_day = $interval->days - 1;

            $output->writeln( "[".date("Y-m-d H:i:s")."] Interval since the beginning of the confirmation period: ".$interval->days );

            switch ($interval->days) {

                // If we are 3 days away from the end of the confirmation period
                case 3:

                    // Send an email to the user.
                    $output->writeln( "[".date("Y-m-d H:i:s")."] We send a request to the webhook to resend an email." );
                    $endpoint = $strime_app_url."app/webhook/resend-email-confirmation-message/".$email_to_confirm->getUser()->getSecretId()."/3";

                    // Set Guzzle
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers_app,
                        'http_errors' => false,
                        'auth' => $nginx_auth
                    ]);

                    $curl_status = $json_response->getStatusCode();
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Guzzle HTTP Status Code: ".$curl_status );
                    $response = json_decode($json_response->getBody());
                    break;

                // If we are 1 day away from the end of the confirmation period
                case $last_day:

                    // Send an email to the user.
                    $output->writeln( "[".date("Y-m-d H:i:s")."] We send a request to the webhook to resend an email." );
                    $endpoint = $strime_app_url."app/webhook/resend-email-confirmation-message/".$email_to_confirm->getUser()->getSecretId()."/1";

                    // Set Guzzle
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('GET', $endpoint, [
                        'headers' => $headers_app,
                        'http_errors' => false,
                        'auth' => $nginx_auth
                    ]);

                    $curl_status = $json_response->getStatusCode();
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Guzzle HTTP Status Code: ".$curl_status );
                    $response = json_decode($json_response->getBody());
                    break;

                default:
                    break;
            }

            // If we have reached the end of the confirmation period
            if( $user_days_to_confirm_email <= $interval->days) {

                // Deactivate the user
                $output->writeln( "[".date("Y-m-d H:i:s")."] We deactivate the user." );
                $user = $email_to_confirm->getUser();
                $user->setStatus("deactivated");
                $em->persist($user);
                $em->flush();

                // Remove the token from the app
                $output->writeln( "[".date("Y-m-d H:i:s")."] We remove the token from the app." );
                $endpoint = $strime_app_url."app/webhook/remove-email-confirmation-token/".$email_to_confirm->getUser()->getSecretId();

                // Set Guzzle
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('GET', $endpoint, [
                    'headers' => $headers_app,
                    'http_errors' => false,
                    'auth' => $nginx_auth
                ]);

                $curl_status = $json_response->getStatusCode();
                $output->writeln( "[".date("Y-m-d H:i:s")."] Guzzle HTTP Status Code: ".$curl_status );
                $response = json_decode($json_response->getBody());

                // Remove the email to confirm entry
                $output->writeln( "[".date("Y-m-d H:i:s")."] We remove the email to confirm entry." );
                $em->remove($email_to_confirm);
                $em->flush();
            }

            $output->writeln( "[".date("Y-m-d H:i:s")."] End of the script" );
        }
    }
}
