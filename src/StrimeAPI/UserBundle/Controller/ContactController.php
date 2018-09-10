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

use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\UserBundle\Entity\User;

class ContactController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/contacts/{user_id}/get")
     * @Template()
     */
    public function getContactsByUserAction(Request $request, $user_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/contacts/{user_id}/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $user = new User;
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        if($user == NULL) {
        	$json['authorization'] = "No user has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the contacts
		$contacts_results = $em->getRepository('StrimeAPIUserBundle:Contact')->findByUser($user);
		$contacts = array();

		// If no offer has been created yet.
		if($contacts_results == NULL) {
			$contacts = "No contact has been found for this user.";
			$json["results"] = $contacts;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($contacts_results as $contact) {
			$contacts[] = array(
				"contact_id" => $contact->getSecretId(),
				"email" => $contact->getEmail(),
				"user" => array(
					"user_id" => $user->getSecretId(),
					"first_name" => $user->getFirstName(),
					"last_name" => $user->getLastName(),
					"email" => $user->getEmail()
				),
				"created_at" => $contact->getCreatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $contacts;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/contact/{secret_id}/get")
     * @Template()
     */
    public function getContactAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'), 
            "version" => $this->container->getParameter('app_version'), 
            "method" => "/contact/{contact_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $contact_details = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $secret_id));
        $contact = array();

        // If we get a result
        if($contact_details != NULL) {

            // Prepare the array containing the results
            $contact = array(
                "contact_id" => $contact_details->getSecretId(),
                "email" => $contact_details->getEmail(),
                "avatar" => $contact_details->getAvatar(),
                "created_at" => $contact_details->getCreatedAt(),
            );

            // Add the results to the response
            $json["results"] = $contact;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $contact = "No contact has been found with this ID.";
            $json["results"] = $contact;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/contact/add")
     * @Template()
     */
    public function addContactAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/contact/add"
    	);

    	// Get the data
    	$email = $request->request->get('email', NULL);
    	$user_id = $request->request->get('user_id', NULL);

        // Change the format of the email
        $email = strtolower($email);

    	// Get the user details
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // If no user has been found with this user ID
        if($user == NULL) {
        	$json['authorization'] = "No user has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Check if this contact already exists
        $contact_exists = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('user' => $user, 'email' => $email));

        // If the contact already exists
        if($contact_exists != NULL) {
        	$json['authorization'] = "This contact has already been saved.";
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If the type of request used is not the one expected.
        if(!$request->isMethod('POST')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["error_message"] = "This is not a POST request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If some data are missing
        elseif(($email == NULL) || ($user_id == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "Some data are missing.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Prepare the entity
            $contact = new Contact;

            // We generate a secret_id
            $secret_id_exists = TRUE;
		    $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
		        $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $secret_id));
            }
            
            // Set the avatar
            $default = "https://www.strime.io/bundles/strimeback/img/player/icon-avatar.png";
            $size = 30;
            $avatar = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;

            // We create the contact
            try {
                $contact->setSecretId($secret_id);
                $contact->setEmail($email);
                $contact->setUser($user);
                $contact->setAvatar($avatar);

                $em->persist($contact);
                $em->flush();

                // Update the user
                $user_nb_contacts = $user->getNbContacts();
                $user_nb_contacts++;
                $user->setNbContacts( $user_nb_contacts );

                $em->persist($user);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["contact_id"] = $contact->getSecretId();

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
     * @Route("/contact/{secret_id}/delete")
     * @Template()
     */
    public function deleteContactAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/contact/{contact_id}/delete"
    	);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $contact = new Contact;

    	// Get the contact
        $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('DELETE')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["error_message"] = "This is not a DELETE request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($contact) || ($contact == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "This contact ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We delete the contact
            try {

                // Update the user
                $user = $contact->getUser();
                if($user != NULL) {
                    $user_nb_contacts = $user->getNbContacts();
                    $user_nb_contacts--;
                    $user->setNbContacts( $user_nb_contacts );

                    $em->persist($user);
                    $em->flush();
                }

                // Delete the contact
            	$em->remove($contact);
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
