<?php

namespace StrimeAPI\StatsBundle\Controller;

use StrimeAPI\GlobalBundle\Controller\TokenAuthenticatedController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use StrimeAPI\GlobalBundle\Token\TokenGenerator;
use StrimeAPI\GlobalBundle\Auth\HeadersAuthorization;

use StrimeAPI\StatsBundle\Entity\Stats;

class AudioStatsController extends Controller implements TokenAuthenticatedController
{
    /**
     * @Route("/audios/number/get")
     * @Template()
     */
    public function getTotalNumberOfAudiosAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/audios/number/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_audios'));

        if($stats == NULL) {
        	$json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Add the results to the response
        $json["results"] = $stats->getData();
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/audio/comments/number/get")
     * @Template()
     */
    public function getTotalNumberOfAudioCommentsAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/audio/comments/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_audio_comments'));

        if($stats == NULL) {
        	$json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Add the results to the response
        $json["results"] = $stats->getData();
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/audios/average-size/get")
     * @Template()
     */
    public function getAverageAudiosSizeAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/stats/audios/average-size/get"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'average_audio_size'));

        if($stats == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Add the results to the response
        $json["results"] = $stats->getData();
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/audios/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfAudiosPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audios/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_audio' );
        $query->from( 'StrimeAPIStatsBundle:NbAudiosPerDay','api_nb_audio' );

        if($start_date != NULL) {
            $query->where('api_nb_audio.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_audio.date_time < :end_date');
            }
            else {
                $query->where('api_nb_audio.date_time < :end_date');
            }
            $query->setParameter('end_date', $end_date);
        }

        $stats = $query->getQuery()->getResult();

        // If no data has been found
        if($stats == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If data has been found
        $results = array();

        // Prepare the array to be returned
        foreach ($stats as $stat) {

            $results[] = array(
                "date_time" => $stat->getDateTime(),
                "nb_audios" => $stat->getNbAudios(),
                "total_nb_audios" => $stat->getTotalNbAudios()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/audio/comments/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfAudioCommentsPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/comments/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_audio_comment' );
        $query->from( 'StrimeAPIStatsBundle:NbAudioCommentsPerDay','api_nb_audio_comment' );

        if($start_date != NULL) {
            $query->where('api_nb_audio_comment.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_audio_comment.date_time < :end_date');
            }
            else {
                $query->where('api_nb_audio_comment.date_time < :end_date');
            }
            $query->setParameter('end_date', $end_date);
        }

        $stats = $query->getQuery()->getResult();

        // If no data has been found
        if($stats == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If data has been found
        $results = array();

        // Prepare the array to be returned
        foreach ($stats as $stat) {

            $results[] = array(
                "date_time" => $stat->getDateTime(),
                "nb_comments" => $stat->getNbComments(),
                "total_nb_comments" => $stat->getTotalNbComments()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/audio/encoding-job-time/get/last/{number}", defaults={"number": NULL})
     * @Template()
     */
    public function getAudioEncodingJobTimeStatsAction(Request $request, $number)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/encoding-job-time/get/last/{number}",
            "method_details" => "{number} must be an integer"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        if($number != NULL) {
            $encoding_job_stats = $em->getRepository('StrimeAPIStatsBundle:AudioEncodingJobTime')->findBy(array(), array('created_at' => 'DESC'), $number);
        }
        else {
            $encoding_job_stats = $em->getRepository('StrimeAPIStatsBundle:AudioEncodingJobTime')->findBy(array(), array('created_at' => 'DESC'));
        }

        // If no data has been found
        if($encoding_job_stats == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Prepare the array to be returned
        $results = array();
        foreach ($encoding_job_stats as $stat) {

            if($stat->getEndTime() != 0) {
                $results[] = array(
                    "total_time" => $stat->getTotalTime(),
                    "size" => $stat->getSize(),
                    "duration" => $stat->getDuration()
                );
            }
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }
}
