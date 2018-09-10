<?php

namespace StrimeAPI\GlobalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Video;
use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\ImageBundle\Entity\Image;
use StrimeAPI\ImageBundle\Entity\ImageComment;

class CronSendCommentsNotificationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:global:send-comments-notifications')
            ->setDescription('Send the delayed notifications for comments')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] CRON which sends delayed notifications for comments" );

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set the entity manager" );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the delay set up
        $delay = $this->getContainer()->getParameter('strime_mail_notifications_delay');
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

        // Get all the comments of videos published within the delay
        $output->writeln( "[".date("Y-m-d H:i:s")."] Gathering all the video comments" );
        $query = $em->createQueryBuilder();
        $query->select( 'api_comment' );
        $query->from( 'StrimeAPIVideoBundle:Comment','api_comment' );
        $query->where( 'api_comment.created_at >= :start_datetime' );
        $query->andWhere( 'api_comment.created_at < :end_datetime' );
        $query->setParameter( 'start_datetime', $start_datetime );
        $query->setParameter( 'end_datetime', $end_datetime );
        $comments_video = $query->getQuery()->getResult();

        if($comments_video == NULL)
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of video comments gathered: 0" );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of video comments gathered: " . count($comments_video) );

        // Get all the comments of images published within the delay
        $output->writeln( "[".date("Y-m-d H:i:s")."] Gathering all the image comments" );
        $query = $em->createQueryBuilder();
        $query->select( 'api_image_comment' );
        $query->from( 'StrimeAPIImageBundle:ImageComment','api_image_comment' );
        $query->where( 'api_image_comment.created_at >= :start_datetime' );
        $query->andWhere( 'api_image_comment.created_at < :end_datetime' );
        $query->setParameter( 'start_datetime', $start_datetime );
        $query->setParameter( 'end_datetime', $end_datetime );
        $comments_image = $query->getQuery()->getResult();

        if($comments_image == NULL)
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of image comments gathered: 0" );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of image comments gathered: " . count($comments_image) );

        // Get all the comments of audio files published within the delay
        $output->writeln( "[".date("Y-m-d H:i:s")."] Gathering all the audio comments" );
        $query = $em->createQueryBuilder();
        $query->select( 'api_audio_comment' );
        $query->from( 'StrimeAPIAudioBundle:AudioComment','api_audio_comment' );
        $query->where( 'api_audio_comment.created_at >= :start_datetime' );
        $query->andWhere( 'api_audio_comment.created_at < :end_datetime' );
        $query->setParameter( 'start_datetime', $start_datetime );
        $query->setParameter( 'end_datetime', $end_datetime );
        $comments_audio = $query->getQuery()->getResult();

        if($comments_audio == NULL)
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of audio comments gathered: 0" );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of audio comments gathered: " . count($comments_audio) );

        // Merge the comments
        $comments = array(
            "video" => $comments_video,
            "image" => $comments_image,
            "audio" => $comments_audio,
        );

        // Prepare an array which will contain all the users to notify, and the comments to send to them
        $notifications = array();

        // Set a flag which will indicate if errors occured
        $error_in_notifications = FALSE;

        $output->writeln( "[".date("Y-m-d H:i:s")."] Setting up the notifications..." );

        if($comments["video"] != NULL) {

            // Browse the comments
            foreach ($comments["video"] as $comment) {

                // If the comment has been posted by someone else than the owner of the video
                if(($comment->getUser() == NULL) || ($comment->getUser()->getId() != $comment->getVideo()->getUser()->getId())) {

                    $output->writeln( "[".date("Y-m-d H:i:s")."] User: ".$comment->getVideo()->getUser()->getEmail() );

                    // If the owner of the video has chosen to receive the notifications with some delay
                    if(strcmp($comment->getVideo()->getUser()->getMailNotification(), "once_a_day") == 0) {

                        // Add it to the list
                        $notifications[ $comment->getVideo()->getUser()->getSecretId() ][] = array(
                            "comment_id" => $comment->getSecretId(),
                            "asset_type" => "video"
                        );
                    }
                }

            }
        }

        if($comments["image"] != NULL) {

            // Browse the comments
            foreach ($comments["image"] as $comment) {

                // If the comment has been posted by someone else than the owner of the image
                if(($comment->getUser() == NULL) || ($comment->getUser()->getId() != $comment->getImage()->getUser()->getId())) {

                    $output->writeln( "[".date("Y-m-d H:i:s")."] User: ".$comment->getImage()->getUser()->getEmail() );

                    // If the owner of the video has chosen to receive the notifications with some delay
                    if(strcmp($comment->getImage()->getUser()->getMailNotification(), "once_a_day") == 0) {

                        // Add it to the list
                        $notifications[ $comment->getImage()->getUser()->getSecretId() ][] = array(
                            "comment_id" => $comment->getSecretId(),
                            "asset_type" => "image"
                        );
                    }
                }

            }
        }

        if($comments["audio"] != NULL) {

            // Browse the comments
            foreach ($comments["audio"] as $comment) {

                // If the comment has been posted by someone else than the owner of the image
                if(($comment->getUser() == NULL) || ($comment->getUser()->getId() != $comment->getAudio()->getUser()->getId())) {

                    $output->writeln( "[".date("Y-m-d H:i:s")."] User: ".$comment->getAudio()->getUser()->getEmail() );

                    // If the owner of the audio file has chosen to receive the notifications with some delay
                    if(strcmp($comment->getAudio()->getUser()->getMailNotification(), "once_a_day") == 0) {

                        // Add it to the list
                        $notifications[ $comment->getAudio()->getUser()->getSecretId() ][] = array(
                            "comment_id" => $comment->getSecretId(),
                            "asset_type" => "audio"
                        );
                    }
                }

            }
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] Nb notifications: " . count($notifications) );


        if(count($notifications) > 0) {

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

                // Set the params
                $params = array(
                    "comments" => json_encode($comments_list)
                );

                // Set the endpoint
                $endpoint = $strime_app_url."app/webhook/send-comments-daily/".$user_id;
                $output->writeln( "[".date("Y-m-d H:i:s")."] Endpoint: " . $endpoint );

                // Send a request on the corresponding webhook
                // Set Guzzle
                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('POST', $endpoint, [
                    'headers' => $headers_app,
                    'http_errors' => false,
                    'auth' => $nginx_auth,
                    'json' => $params
                ]);

                $curl_status = $json_response->getStatusCode();
                $output->writeln( "[".date("Y-m-d H:i:s")."] Guzzle HTTP Status Code: ".$curl_status );
                $response = json_decode($json_response->getBody());

                // If an error occured in the request, set the flag to TRUE
                if($curl_status != 200)
                    $error_in_notifications = TRUE;
            }
        }

        if($error_in_notifications == FALSE)
            $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The CRON has been properly executed." );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] ERROR: Errors occured while sending the emails." );
    }
}
