<?php

namespace StrimeAPI\AudioBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\AudioBundle\Entity\Audio;
use StrimeAPI\AudioBundle\Entity\AudioComment;

class CronSendAudioCommentsInstantNotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:audio:send-comments-instant-notifications')
            ->setDescription('Send the instant notifications for the audio comments')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] CRON which sends instant notifications for the audio comments" );

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set the entity manager" );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the delay set up
        $delay = $this->getContainer()->getParameter('strime_mail_instant_notifications_delay');
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the delay: " . $delay );

        // Get the unix timestamp of the date without this delay
        $end_timestamp = time();
        $start_timestamp = $end_timestamp - $delay;
        $output->writeln( "[".date("Y-m-d H:i:s")."] Current time: " . $end_timestamp );
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the time as of which we have to gather comments: " . $start_timestamp );

        // Create the DateTime based on this timestamp
        $start_datetime = new \DateTime();
        $start_datetime->setTimestamp($start_timestamp);
        $end_datetime = new \DateTime();
        $end_datetime->setTimestamp($end_timestamp);
        $output->writeln( "[".date("Y-m-d H:i:s")."] Start timestamp: " . $start_datetime->format('Y-m-d H:i:s') );
        $output->writeln( "[".date("Y-m-d H:i:s")."] End timestamp: " . $end_datetime->format('Y-m-d H:i:s') );

        // Get all the comments published within the delay
        $output->writeln( "[".date("Y-m-d H:i:s")."] Gathering all the comments" );
        $query = $em->createQueryBuilder();
        $query->select( 'api_audio_comment' );
        $query->from( 'StrimeAPIAudioBundle:AudioComment','api_audio_comment' );
        $query->where( 'api_audio_comment.created_at >= :start_datetime' );
        $query->andWhere( 'api_audio_comment.created_at < :end_datetime' );
        $query->setParameter( 'start_datetime', $start_datetime );
        $query->setParameter( 'end_datetime', $end_datetime );
        $comments = $query->getQuery()->getResult();

        if($comments == NULL)
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of comments gathered: 0" );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of comments gathered: " . count($comments) );

        // Prepare an array which will contain all the users to notify, and the comments to send to them
        $notifications = array();

        // Set a flag which will indicate if errors occured
        $error_in_notifications = FALSE;

        if($comments != NULL) {

            $output->writeln( "[".date("Y-m-d H:i:s")."] Setting up the notifications..." );

            // Browse the comments
            foreach ($comments as $comment) {

                // If the comment has been posted by someone else than the owner of the audio
                // And if the owner of the audio wants to be notified right away
                if((strcmp($comment->getAudio()->getUser()->getMailNotification(), "now") == 0)
                    && (($comment->getUser() == NULL) || ($comment->getUser()->getId() != $comment->getAudio()->getUser()->getId()))) {

                    $output->writeln( "[".date("Y-m-d H:i:s")."] User: ".$comment->getAudio()->getUser()->getEmail() );
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Comment ID: ".$comment->getSecretId() );

                    // If the owner of the audio has chosen to receive the notifications right away
                    // Add it to the list
                    $notifications[ $comment->getAudio()->getUser()->getSecretId() ][] = $comment->getSecretId();
                }

            }

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

            // Browse the notifications
            $output->writeln( "[".date("Y-m-d H:i:s")."] Sending requests on the webhook..." );
            foreach ($notifications as $user_id => $comments_list) {

                foreach ($comments_list as $comment) {

                    // Set the endpoint
                    $endpoint = $strime_app_url."app/webhook/send-comments-right-away/".$user_id."/audio/".$comment;
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Endpoint: " . $endpoint );

                    // Send a request on the corresponding webhook
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

                    // If an error occured in the request, set the flag to TRUE
                    if($curl_status != 200)
                        $error_in_notifications = TRUE;
                }
            }
        }

        if($error_in_notifications == FALSE)
            $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The CRON has been properly executed." );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] ERROR: Errors occured while sending the emails." );
    }
}
