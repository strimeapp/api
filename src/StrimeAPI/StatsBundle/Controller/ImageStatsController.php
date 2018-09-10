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

class ImageStatsController extends Controller implements TokenAuthenticatedController
{
    /**
     * @Route("/images/number/get")
     * @Template()
     */
    public function getTotalNumberOfImagesAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/images/number/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_images'));

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
     * @Route("/image/comments/number/get")
     * @Template()
     */
    public function getTotalNumberOfImageCommentsAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/image/comments/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_image_comments'));

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
     * @Route("/images/average-size/get")
     * @Template()
     */
    public function getAverageImagesSizeAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/stats/images/average-size/get"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'average_image_size'));

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
     * @Route("/images/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfImagesPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/images/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_image' );
        $query->from( 'StrimeAPIStatsBundle:NbImagesPerDay','api_nb_image' );

        if($start_date != NULL) {
            $query->where('api_nb_image.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_image.date_time < :end_date');
            }
            else {
                $query->where('api_nb_image.date_time < :end_date');
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
                "nb_images" => $stat->getNbImages(),
                "total_nb_images" => $stat->getTotalNbImages()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/image/comments/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfImageCommentsPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/comments/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_image_comment' );
        $query->from( 'StrimeAPIStatsBundle:NbImageCommentsPerDay','api_nb_image_comment' );

        if($start_date != NULL) {
            $query->where('api_nb_image_comment.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_image_comment.date_time < :end_date');
            }
            else {
                $query->where('api_nb_image_comment.date_time < :end_date');
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
}
