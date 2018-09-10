<?php

namespace StrimeAPI\VideoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Video;
use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\VideoBundle\Entity\CommentWithThumbInError;

class CronGenerateCommentThumbCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:comment:generate-thumbnail')
            ->setDescription('Send requests to the encoding API to generate comment thumbnails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] CRON which sends requests to the encoding API to generate thumbnails for the comments" );

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set the entity manager" );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get all the comments which have a NULL value for s3_url
        $comments = $em->getRepository('StrimeAPIVideoBundle:Comment')->findBy(array('s3_url' => NULL));
        $output->writeln( "[".date("Y-m-d H:i:s")."] Number of comments gathered: " . count($comments) );

        // Get the comments that are in error with less than 3 attemps and with no S3 URL
        $query = $em->createQueryBuilder();
        $query->select( 'api_comment_with_thumb_in_error' );
        $query->from( 'StrimeAPIVideoBundle:CommentWithThumbInError','api_comment_with_thumb_in_error' );
        $query->where('api_comment_with_thumb_in_error.nb_attempts < :nb_attempts');
        $query->setParameter( 'nb_attempts', 3 );
        $comments_in_error = $query->getQuery()->getResult();
        $output->writeln( "[".date("Y-m-d H:i:s")."] Number of comments gathered: " . count($comments_in_error) );

        // Set the flag
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set the flag" );
        $error_in_thumbs_generation = FALSE;

        if($comments != NULL) {

            // Get the webhook parameters
            $output->writeln( "[".date("Y-m-d H:i:s")."] Get the webhook parameters." );
            $strime_encoding_api_url = $this->getContainer()->getParameter('strime_encoding_api_url');
            $strime_encoding_api_token = $this->getContainer()->getParameter('strime_encoding_api_token');

            // Set the headers
            $output->writeln( "[".date("Y-m-d H:i:s")."] Set the headers." );
            $headers_app = array(
                'Accept' => 'application/json',
                'X-Auth-Token' => $strime_encoding_api_token,
                'Content-type' => 'application/json'
            );

            // Set the endpoint
            $endpoint = $strime_encoding_api_url."comment/generate-thumbnail";
            $output->writeln( "[".date("Y-m-d H:i:s")."] Endpoint: " . $endpoint );

            $output->writeln( "[".date("Y-m-d H:i:s")."] Sending the requests..." );

            // Browse the comments
            foreach ($comments as $comment) {

                // Make sure that the comment has not been in error yet
                $comment_was_in_error = $em->getRepository('StrimeAPIVideoBundle:CommentWithThumbInError')->findBy(array('comment' => $comment, 'nb_attempts' => 3));

                if($comment_was_in_error == NULL) {

                    // Set the params
                    $params = array(
                        "comment_id" => $comment->getSecretId(),
                        "video_id" => $comment->getVideo()->getSecretId(),
                        "timecode" => $comment->getTime()
                    );

                    // Send a request on the corresponding webhook
                    // Set Guzzle
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('POST', $endpoint, [
                        'headers' => $headers_app,
                        'http_errors' => false,
                        'json' => $params
                    ]);

                    $curl_status = $json_response->getStatusCode();
                    $response = json_decode($json_response->getBody());

                    // If an error occured in the request, do something
                    if($curl_status != 200) {

                        // Add the comment in the list of comments in error
                        $comment_is_already_in_error = FALSE;

                        // Browse the list of comments in error
                        foreach ($comments_in_error as $comment_in_error) {

                            // If the comment is already in this list
                            if($comment_in_error->getComment()->getId() == $comment->getId()) {

                                // Update the flag
                                $comment_is_already_in_error = TRUE;

                                // Update the entity by incrementing the number of attempts
                                $nb_attempts = $comment_in_error->getNbAttempts() + 1;
                                $comment_in_error->setNbAttempts( $nb_attempts );
                                $em->persist( $comment_in_error );
                                $em->flush();
                            }
                        }

                        // If the comment wasn't in the list
                        if( !$comment_is_already_in_error ) {

                            // Create a new entity in the table of comments in error
                            $comment_in_error = new CommentWithThumbInError();
                            $comment_in_error->setComment( $comment );
                            $comment_in_error->setNbAttempts( 1 );
                            $em->persist( $comment_in_error );
                            $em->flush();
                        }

                        // Set the flag
                        $error_in_thumbs_generation = TRUE;
                    }

                    $output->writeln( "[".date("Y-m-d H:i:s")."] - Comment: ".$comment->getSecretId().", result: ".$curl_status );
                }

                $output->writeln( "[".date("Y-m-d H:i:s")."] <error>- 3 attemps in error for this comment</error>" );
            }
        }

        if($error_in_thumbs_generation == FALSE)
            $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The CRON has been properly executed." );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] ERROR: Errors occured while generating the thumbnails." );
    }
}
