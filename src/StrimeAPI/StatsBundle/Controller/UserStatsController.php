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
use StrimeAPI\StatsBundle\Entity\NbUsersPerDay;
use StrimeAPI\StatsBundle\Entity\PercentageActiveUsersPerDay;

class UserStatsController extends Controller implements TokenAuthenticatedController
{
    /**
     * @Route("/users/number/get")
     * @Template()
     */
    public function getTotalNumberOfUsersAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/users/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users'));

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
     * @Route("/users-google-signin/number/get")
     * @Template()
     */
    public function getTotalNumberOfUsersUsingGoogleSigninAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/users-google-signin/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_google_signin'));

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
     * @Route("/users-facebook-signin/number/get")
     * @Template()
     */
    public function getTotalNumberOfUsersUsingFacebookSigninAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/users-facebook-signin/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_facebook_signin'));

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
     * @Route("/users-slack/number/get")
     * @Template()
     */
    public function getTotalNumberOfUsersUsingSlackAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/users-slack/number/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_slack'));

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
     * @Route("/contacts/number/get")
     * @Template()
     */
    public function getTotalNumberOfContactsAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/stats/contacts/number/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_contacts'));

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
     * @Route("/users/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfUsersPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/users/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_user' );
        $query->from( 'StrimeAPIStatsBundle:NbUsersPerDay','api_nb_user' );

        if($start_date != NULL) {
            $query->where('api_nb_user.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_user.date_time < :end_date');
            }
            else {
                $query->where('api_nb_user.date_time < :end_date');
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
                "nb_users" => $stat->getNbUsers(),
                "total_nb_users" => $stat->getTotalNbUsers()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/users/active/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfActiveUsersPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/users/active/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_percentage_active_user' );
        $query->from( 'StrimeAPIStatsBundle:PercentageActiveUsersPerDay','api_percentage_active_user' );

        if($start_date != NULL) {
            $query->where('api_percentage_active_user.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_percentage_active_user.date_time < :end_date');
            }
            else {
                $query->where('api_percentage_active_user.date_time < :end_date');
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
                "percentage_active_users" => $stat->getPercentageActiveUsers()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/users/per-offer/get")
     * @Template()
     */
    public function getNumberOfUsersPerOfferAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/stats/users/per-offer/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_per_offer'));

        if($stats == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Add the results to the response
        $json["results"] = $stats->getDataJson();
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/users/per-locale/get")
     * @Template()
     */
    public function getNumberOfUsersPerLocaleAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/stats/users/per-locale/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $stats = new Stats;
        $stats = $em->getRepository('StrimeAPIStatsBundle:Stats')->findOneBy(array('name' => 'number_of_users_per_locale'));

        if($stats == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Add the results to the response
        $json["results"] = $stats->getDataJson();
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/addresses/list/get")
     * @Template()
     */
    public function getAddressesListAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/stats/addresses/list/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the users which have addresses set
        $query = $em->createQueryBuilder();
        $query->select( 'api_user_address' );
        $query->from( 'StrimeAPIUserBundle:Address','api_user_address' );
        $query->where('api_user_address.latitude IS NOT NULL');
        $query->andWhere('api_user_address.longitude IS NOT NULL');
        $addresses = $query->getQuery()->getResult();

        if($addresses == NULL) {
            $json['authorization'] = "No data has been found.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }


        // Set the results
        $results = array();

        foreach ($addresses as $address) {
            $results[] = array(
                "latitude" => $address->getLatitude(),
                "longitude" => $address->getLongitude(),
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/contacts/per-day/get/{start_date}/{end_date}", defaults={"start_date": NULL, "end_date": NULL})
     * @Template()
     */
    public function getNumberOfContactsPerDayAction(Request $request, $start_date, $end_date)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/contacts/per-day/get/{start_date}/{end_date}",
            "method_details" => "start_date and end_date must be unix time integers"
        );

        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();


        // Select the requested stats
        $query = $em->createQueryBuilder();
        $query->select( 'api_nb_contacts' );
        $query->from( 'StrimeAPIStatsBundle:NbContactsPerDay','api_nb_contacts' );

        if($start_date != NULL) {
            $query->where('api_nb_contact.date_time >= :start_date');
            $query->setParameter('start_date', $start_date);
        }
        if($end_date != NULL) {
            if($start_date != NULL) {
                $query->andWhere('api_nb_contacts.date_time < :end_date');
            }
            else {
                $query->where('api_nb_contacts.date_time < :end_date');
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
                "nb_contacts" => $stat->getNbContacts(),
                "total_nb_contacts" => $stat->getTotalNbContacts()
            );
        }

        // Add the results to the response
        $json["results"] = $results;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }
}
