<?php

namespace StrimeAPI\UserBundle\Controller;

use StrimeAPI\GlobalBundle\Controller\TokenAuthenticatedController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use StrimeAPI\GlobalBundle\Token\TokenGenerator;
use StrimeAPI\GlobalBundle\Auth\HeadersAuthorization;

use StrimeAPI\UserBundle\Entity\Offer;

class OfferController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/offers/get")
     * @Template()
     */
    public function getOffersAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/offers/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();
		$offers_results = $em->getRepository('StrimeAPIUserBundle:Offer')->findAll();
		$offers = array();

		// If we get a result
		if($offers_results != NULL) {

			// Prepare the array containing the results
			foreach ($offers_results as $offer) {
				$offers[] = array(
					"offer_id" => $offer->getSecretId(),
					"name" => $offer->getName(),
					"price" => $offer->getPrice(),
                    "storage_allowed" => $offer->getStorageAllowed(),
                    "nb_videos" => $offer->getNbVideos(),
                    "created_at" => $offer->getCreatedAt(),
					"updated_at" => $offer->getUpdatedAt()
				);
			}

	        // Add the results to the response
	        $json["results"] = $offers;
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

		// If no offer has been created yet.
		else {
			$json["message"] = "No offer has been created yet.";
			$json["results"] = array();
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

        // Return the results
        return $response;
    }


    /**
     * @Route("/offer/{secret_id}/get")
     * @Template()
     */
    public function getOfferAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/offer/{offer_id}/get"
    	);

    	// Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
		$offer_details = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $secret_id));
		$offer = array();

		// If we get a result
		if($offer_details != NULL) {

			// Prepare the array containing the results
			$offer = array(
				"offer_id" => $offer_details->getSecretId(),
				"name" => $offer_details->getName(),
				"price" => $offer_details->getPrice(),
                "storage_allowed" => $offer_details->getStorageAllowed(),
                "nb_videos" => $offer_details->getNbVideos(),
				"updated_at" => $offer_details->getUpdatedAt()
			);

	        // Add the results to the response
	        $json["results"] = $offer;
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
	    }

	    // If no offer has been found with this ID.
	    else {
	    	$offer = "No offer has been found with this ID.";
			$json["results"] = $offer;
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
	    }

        // Return the results
        return $response;
    }


    /**
     * @Route("/offer/add")
     * @Template()
     */
    public function addOfferAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/offer/add"
    	);

    	// Get the data
    	$name = $request->request->get('name', NULL);
    	$price = $request->request->get('price', NULL);
        $nb_videos = $request->request->get('nb_videos', 0);
        $storage_allowed = $request->request->get('storage_allowed', 0);

        // If the type of request used is not the one expected.
        if(!$request->isMethod('POST')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a POST request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If some data are missing
        elseif(($name == NULL) || ($price == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "Some data are missing.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Prepare the entity
            $em = $this->getDoctrine()->getManager();
            $offer = new Offer;

            // We generate a secret_id
            $secret_id_exists = TRUE;
		    $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
		        $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $secret_id));
            }

            // We create the offer
            try {
                $offer->setSecretId($secret_id);
                $offer->setName($name);
                $offer->setPrice($price);
                $offer->setNbVideos($nb_videos);
                $offer->setStorageAllowed($storage_allowed);

                $em->persist($offer);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["offer_id"] = $offer->getSecretId();

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 201, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["message"] = "An error occured while inserting data into the database.";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/offer/{secret_id}/edit")
     * @Template()
     */
    public function editOfferAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/offer/{offer_id}/edit"
    	);

    	// Get the data
    	$name = $request->request->get('name', NULL);
    	$price = $request->request->get('price', NULL);
        $storage_allowed = $request->request->get('storage_allowed', NULL);
        $nb_videos = $request->request->get('nb_videos', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $offer = new Offer;

    	// Get the offer
        $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('PUT')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a PUT request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($offer) || ($offer == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This offer ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the offer
            try {
            	if($name != NULL)
                	$offer->setName($name);

                if($price != NULL)
                	$offer->setPrice($price);

                if($storage_allowed != NULL)
                    $offer->setStorageAllowed($storage_allowed);

                if($nb_videos != NULL)
                    $offer->setStorageAllowed($nb_videos);

                $em->persist($offer);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "200";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["message"] = "An error occured while editing data in the database.";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/offer/{secret_id}/delete")
     * @Template()
     */
    public function deleteOfferAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/offer/{offer_id}/delete"
    	);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $offer = new Offer;

    	// Get the offer
        $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('DELETE')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a DELETE request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($offer) || ($offer == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This offer ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We delete the offer
            try {
            	$em->remove($offer);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "204";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 204, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["message"] = "An error occured while deleting data from the database.";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }
}
