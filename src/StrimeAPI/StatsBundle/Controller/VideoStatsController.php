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

class VideoStatsController extends Controller implements TokenAuthenticatedController
{
    /**
     * @Route("/videos/number/get")
     * @Template()
     */
    public function getTotalNumberOfVideosAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/videos/number/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_videos'));

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
     * @Route("/comments/number/get")
     * @Template()
     */
    public function getTotalNumberOfCommentsAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/comments/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_comments'));

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
     * @Route("/videos/average-size/get")
     * @Template()
     */
    public function getAverageVideoSizeAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/stats/videos/average-size/get"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'average_video_size'));

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
     * @Route("/videos/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfVideosPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/videos/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_video' );
        $query->from( 'StrimeAPIStatsBundle:NbVideosPerDay','api_nb_video' );

        if($start_date != NULL) {
            $query->where('api_nb_video.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_video.date_time < :end_date');
            }
            else {
                $query->where('api_nb_video.date_time < :end_date');
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
                "nb_videos" => $stat->getNbVideos(),
                "total_nb_videos" => $stat->getTotalNbVideos()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/projects/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfProjectsPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/projects/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_project' );
        $query->from( 'StrimeAPIStatsBundle:NbProjectsPerDay','api_nb_project' );

        if($start_date != NULL) {
            $query->where('api_nb_project.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_project.date_time < :end_date');
            }
            else {
                $query->where('api_nb_project.date_time < :end_date');
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
                "nb_projects" => $stat->getNbProjects(),
                "total_nb_projects" => $stat->getTotalNbProjects()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/comments/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfCommentsPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/comments/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_comment' );
        $query->from( 'StrimeAPIStatsBundle:NbCommentsPerDay','api_nb_comment' );

        if($start_date != NULL) {
            $query->where('api_nb_comment.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_comment.date_time < :end_date');
            }
            else {
                $query->where('api_nb_comment.date_time < :end_date');
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
     * @Route("/encoding-job-time/get/last/{number}", defaults={"number": NULL})
     * @Template()
     */
    public function getEncodingJobTimeStatsAction(Request $request, $number)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job-time/get/last/{number}",
            "method_details" => "{number} must be an integer"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        if($number != NULL) {
            $encoding_job_stats = $em->getRepository('StrimeAPIStatsBundle:EncodingJobTime')->findBy(array(), array('created_at' => 'DESC'), $number);
        }
        else {
            $encoding_job_stats = $em->getRepository('StrimeAPIStatsBundle:EncodingJobTime')->findBy(array(), array('created_at' => 'DESC'));
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
