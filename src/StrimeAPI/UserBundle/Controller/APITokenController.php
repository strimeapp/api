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

use StrimeAPI\UserBundle\Entity\Token;
use StrimeAPI\UserBundle\Entity\User;

class APITokenController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/token/{token}/get")
     * @Template()
     */
    public function getTokenAction(Request $request, $token)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/token/{token}/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();
		$token_details = $em->getRepository('StrimeAPIUserBundle:Token')->findOneBy(array('token' => $token));
		$token = array();

		// If we get a result
		if($token_details != NULL) {

            // Get the details of the user associated to this token
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $token_details->getUser()));

            // Prepare the token content
            if($user != NULL) {
                $user_content = array(
                    "user_id" => $user->getSecretId(),
                    "first_name" => $user->getFirstName(), 
                    "last_name" => $user->getLastName()
                );
            }
            else {
                $user_content = array();
            }

			// Prepare the array containing the results
			$token = array(
				"token" => $token_details->getToken(),
				"user" => $user_content,
				"created_at" => $token_details->getCreatedAt()
			);

	        // Add the results to the response
	        $json["results"] = $token;
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
	    }

	    // If no token has been found with this value.
	    else {
	    	$json["message"] = "No token has been found with this value.";
			$json["results"] = array();
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
	    }

        // Return the results
        return $response;
    }


    /**
     * @Route("/token/add")
     * @Template()
     */
    public function addAPITokenAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/token/add"
    	);

    	// Get the data
    	$user_id = $request->request->get('user_id', NULL);

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
        elseif($user_id == NULL) {
            
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
            $user = new User;
            $token = new Token;

            // Get the user details
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

            // If no user has been found with this user ID
            if($user == FALSE) {

                // Set the content of the response
                $json["message"] = "No user has been found with this ID.";
                $json["results"] = array();

                // Create the response object and initialize it
                $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

                return $response;
                exit;
            }

            // Else, we have found the corresponding user
            // Check if a token has already been generated for this user
            $check_token_exists = $em->getRepository('StrimeAPIUserBundle:Token')->findOneBy(array('user' => $user->getId()));

            // If there is already a token for this user
            if($check_token_exists != FALSE) {

                // Set the content of the response
                $json["status"] = "error";
                $json["response_code"] = "400";
                $json["message"] = "This user already has a token.";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

                return $response;
                exit;
            }

            // Otherwise, we create a token for this user
            // Generate a new token
            $token_generator = new TokenGenerator();
            $token_value_exists = NULL;
            $token_value = $token_generator->generateToken(50);
            while($token_value_exists != NULL) {
		        $token_value = $token_generator->generateToken(10);
                $token_value_exists = $em->getRepository('StrimeAPIUserBundle:Token')->findOneBy(array('token' => $token_value));
            }

            // We create the offer
            try {
                $token->setToken($token_value);
                $token->setUser($user);

                $em->persist($token);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["token"] = $token_value;

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
}
