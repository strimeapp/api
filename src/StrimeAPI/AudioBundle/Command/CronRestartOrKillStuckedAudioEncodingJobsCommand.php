<?php

namespace StrimeAPI\AudioBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\AudioBundle\Entity\AudioEncodingJob;

class CronRestartOrKillStuckedAudioEncodingJobsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:audio-encoding-job:restart-or-kill-if-stucked')
            ->setDescription('Restart or kill the encoding jobs that are stucked at 5%')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln( "[".date("Y-m-d H:i:s")."] CRON which restarts or kills the encoding jobs that are stucked at 5%." );

        // Set the entity manager
        $output->writeln( "[".date("Y-m-d H:i:s")."] Set the entity manager" );
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the delay set up
        $delay = $this->getContainer()->getParameter('strime_encoding_api_delay_before_restarting_job');
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the delay: " . $delay );

        // Get the unix timestamp of the date without this delay
        $end_timestamp = time();
        $start_timestamp = $end_timestamp - $delay;
        $output->writeln( "[".date("Y-m-d H:i:s")."] Current time: " . $end_timestamp );
        $output->writeln( "[".date("Y-m-d H:i:s")."] Get the time as of which we have to gather jobs: " . $start_timestamp );

        // Get all the jobs started before the delay and with a progression at 5%
        $output->writeln( "[".date("Y-m-d H:i:s")."] Gathering all the encoding jobs" );
        $query = $em->createQueryBuilder();
        $query->select( 'api_audio_encoding_job' );
        $query->from( 'StrimeAPIAudioBundle:AudioEncodingJob','api_audio_encoding_job' );
        $query->where( 'api_audio_encoding_job.restart_time < :restart_time' );
        $query->andWhere( 'api_audio_encoding_job.status = :status' );
        $query->andWhere( 'api_audio_encoding_job.started = :started' );
        $query->setParameter( 'restart_time', $start_timestamp );
        $query->setParameter( 'status', 5 );
        $query->setParameter( 'started', 1 );
        $encoding_jobs = $query->getQuery()->getResult();

        if($encoding_jobs == NULL)
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of jobs gathered: 0" );
        else
            $output->writeln( "[".date("Y-m-d H:i:s")."] Number of jobs gathered: " . count($encoding_jobs) );

        if($encoding_jobs != NULL) {

            $output->writeln( "[".date("Y-m-d H:i:s")."] Processing the jobs..." );

            // Browse the encoding jobs
            foreach ($encoding_jobs as $job) {

                // If the encoding job has never been restarted, restart it up to 3 times.
                if($job->getRestarted() < 3) {

                    $nb_of_restart = $job->getRestarted() + 1;

                    $output->writeln( "[".date("Y-m-d H:i:s")."] Job ID: " . $job->getSecretId() );
                    $output->writeln( "[".date("Y-m-d H:i:s")."] - restart" );
                    $job->setRestarted( $nb_of_restart );
                    $job->setRestartTime( time() );
                    $job->setStarted( 0 );
                    $job->setStatus( 0 );
                    $job->setErrorCode( NULL );
                    $em->persist( $job );
                    $em->flush();
                }

                // Else, if the encoding job has already been restarted once, we simply kill it.
                else {

                    // Clean the EncodingJobTime table
                    $encoding_job_time = $em->getRepository('StrimeAPIStatsBundle:AudioEncodingJobTime')->findOneBy(array('audio' => $job->getAudio()));

                    if($encoding_job_time != NULL) {
                        $output->writeln( "[".date("Y-m-d H:i:s")."] - kill stats time" );

                        $em->remove($encoding_job_time);
                        $em->flush();
                    }

                    // Send a request to delete the audio
                    $output->writeln( "[".date("Y-m-d H:i:s")."] - delete audio" );

                    // Get the webhook parameters
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Get the webhook parameters." );
                    $strime_api_url = $this->getContainer()->getParameter('strime_api_url');
                    $strime_api_token = $this->getContainer()->getParameter('strime_api_token');

                    // Set the headers
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Set the headers." );
                    $headers_app = array(
                        'Accept' => 'application/json',
                        'X-Auth-Token' => $strime_api_token,
                        'Content-type' => 'application/json'
                    );

                    // Set the endpoint
                    $endpoint = $strime_api_url."audio/".$job->getAudio()->getSecretId()."/delete";

                    // Send a request on the corresponding webhook
                    // Set Guzzle
                    $client = new \GuzzleHttp\Client();
                    $json_response = $client->request('DELETE', $endpoint, [
                        'headers' => $headers_app,
                        'http_errors' => false
                    ]);

                    $curl_status = $json_response->getStatusCode();
                    $output->writeln( "[".date("Y-m-d H:i:s")."] Guzzle HTTP Status Code: ".$curl_status );
                    $response = json_decode($json_response->getBody());

                    // If the audio has been properly deleted
                    if($curl_status == 204) {

                        // Send a request to the app to send an email to the user.
                        // Get the webhook parameters
                        $output->writeln( "[".date("Y-m-d H:i:s")."] - getting the webhook parameters" );
                        $strime_app_url = $this->getContainer()->getParameter('strime_app_url');
                        $strime_app_token = $this->getContainer()->getParameter('strime_app_token');

                        // Set the headers
                        $output->writeln( "[".date("Y-m-d H:i:s")."] - setting the new headers." );
                        $headers_app = array(
                            'Accept' => 'application/json',
                            'X-Auth-Token' => $strime_app_token,
                            'Content-type' => 'application/json'
                        );

                        // Set nginx auth
                        $output->writeln( "[".date("Y-m-d H:i:s")."] - setting nginx auth." );
                        $nginx_auth = NULL;
                        if(strcmp( $this->getContainer()->get( 'kernel' )->getEnvironment(), "test" ) == 0) {
                            $strime_app_nginx_username = $this->getContainer()->getParameter('strime_app_nginx_username');
                            $strime_app_nginx_pwd = $this->getContainer()->getParameter('strime_app_nginx_pwd');
                            $nginx_auth = [$strime_app_nginx_username, $strime_app_nginx_pwd];
                        }

                        // Set the endpoint
                        $endpoint = $strime_app_url."app/webhook/encoding/killed";

                        // Set the parameters
                        $params = array(
                            "first_name" => $job->getUser()->getFirstName(),
                            "last_name" => $job->getUser()->getLastName(),
                            "asset" => $job->getAudio()->getName(),
                            "asset_type" => "audio",
                            "locale" => $job->getUser()->getLocale(),
                            "email" => $job->getUser()->getEmail(),
                        );

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
                        $output->writeln( "[".date("Y-m-d H:i:s")."] - Guzzle HTTP Status Code: ".$curl_status );
                        $response = json_decode($json_response->getBody());

                        // If an error occured in the request, set the flag to TRUE
                        if($curl_status != 200) {
                            $output->writeln( "[".date("Y-m-d H:i:s")."] - ERROR in the notification" );
                        }

                        $output->writeln( "[".date("Y-m-d H:i:s")."] - kill" );

                        // Delete the job
                        $em->remove($job);
                        $em->flush();
                    }

                    // If an error occured in deleting the audio
                    else {
                        $output->writeln( "[".date("Y-m-d H:i:s")."] - ERROR: audio not deleted (cURL status: " . $curl_status . ")" );
                    }
                }
            }
        }
    }
}
