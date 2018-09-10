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

use StrimeAPI\UserBundle\Entity\Coupon;
use StrimeAPI\UserBundle\Entity\Offer;

class CouponController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/coupons/get")
     * @Template()
     */
    public function getCouponsAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/coupons/get"
    	);

    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();
		$coupons_results = $em->getRepository('StrimeAPIUserBundle:Coupon')->findAll();
		$coupons = array();

		// If we get a result
		if($coupons_results != NULL) {

			// Prepare the array containing the results
			foreach ($coupons_results as $coupon) {

                // Get the offers associated to this coupon
                $offers = $coupon->getOffers();

                if( $offers != NULL ) {

                    $offers_list = array();

                    // Foreach contact, add its email and ID to the result
                    foreach ($offers as $offer) {
                        
                        $offers_list[] = array(
                            "offer_id" => $offer->getSecretId(),
                            "name" => $offer->getName(),
                            "price" => $offer->getPrice(),
                            "storage_allowed" => $offer->getStorageAllowed(),
                            "nb_videos" => $offer->getNbVideos(),
                        );
                    }
                }
                else {
                    $offers_list = NULL;
                }

				$coupons[] = array(
					"stripe_id" => $coupon->getStripeId(),
					"offers" => $offers_list,
                    "created_at" => $coupon->getCreatedAt(),
					"updated_at" => $coupon->getUpdatedAt()
				);
			}

	        // Add the results to the response
	        $json["results"] = $coupons;
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

		// If no coupon has been created yet.
		else {
			$json["message"] = "No coupon has been created yet.";
			$json["results"] = array();
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

        // Return the results
        return $response;
    }


    /**
     * @Route("/coupon/{stripe_id}/get")
     * @Template()
     */
    public function getCouponAction(Request $request, $stripe_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/coupon/{stripe_id}/get"
    	);

    	// Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
		$coupon_details = $em->getRepository('StrimeAPIUserBundle:Coupon')->findOneBy(array('stripe_id' => $stripe_id));
		$coupon = array();

		// If we get a result
		if($coupon_details != NULL) {

            // Get the offers associated to this coupon
            $offers = $coupon_details->getOffers();

            if( $offers != NULL ) {

                $offers_list = array();

                // Foreach contact, add its email and ID to the result
                foreach ($offers as $offer) {
                    
                    $offers_list[] = array(
                        "offer_id" => $offer->getSecretId(),
                        "name" => $offer->getName(),
                        "price" => $offer->getPrice(),
                        "storage_allowed" => $offer->getStorageAllowed(),
                        "nb_videos" => $offer->getNbVideos(),
                    );
                }
            }
            else {
                $offers_list = NULL;
            }

			// Prepare the array containing the results
			$coupon = array(
				"stripe_id" => $coupon_details->getStripeId(),
				"offers" => $offers_list,
				"updated_at" => $coupon_details->getUpdatedAt()
			);

	        // Add the results to the response
	        $json["results"] = $coupon;
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
	    }

	    // If no coupon has been found with this ID.
	    else {
	    	$offer = "No coupon has been found with this ID.";
			$json["results"] = $coupon;
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
	    }

        // Return the results
        return $response;
    }


    /**
     * @Route("/coupon/add")
     * @Template()
     */
    public function addCouponAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/offer/add"
    	);

    	// Get the data
    	$stripe_id = $request->request->get('stripe_id', NULL);
    	$offers = $request->request->get('offers', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();

        // Check if a coupon with this ID already exists
        $coupon = $em->getRepository('StrimeAPIUserBundle:Coupon')->findOneBy(array('stripe_id' => $stripe_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('POST')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a POST request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If a coupon with this ID already exists
        elseif($coupon != NULL) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This ID is already used.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Prepare the coupon object
            $coupon = new Coupon;

            // We create the offer
            try {
                $coupon->setStripeId($stripe_id);

                // Get the offers and add them to the coupon
                if($offers != NULL) {
                    foreach ($offers as $offer_id) {
                        
                        $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $offer_id));

                        if($offer != NULL) {
                            $coupon->addOffer($offer);
                        }
                    }
                }

                $em->persist($coupon);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["stripe_id"] = $stripe_id;

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
     * @Route("/coupon/{stripe_id}/edit")
     * @Template()
     */
    public function editCouponAction(Request $request, $stripe_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/coupon/{stripe_id}/edit"
    	);

    	// Get the data
    	$offers = $request->request->get('offers', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();

    	// Get the coupon
        $coupon = $em->getRepository('StrimeAPIUserBundle:Coupon')->findOneBy(array('stripe_id' => $stripe_id));

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
        elseif(!is_object($coupon) || ($coupon == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This coupon ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the offer
            try {
            	if($offers != NULL) {

                    // Edit the contacts if needed
                    $current_offers = $coupon->getOffers();

                    foreach ($offers as $offer_id) {
                        $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $offer_id));

                        if($offer != NULL) {
                            $offer_already_exists = FALSE;

                            foreach ($current_offers as $current_offer) {
                                if($current_offer->getId() == $offer->getId()) {
                                    $offer_already_exists = TRUE;
                                }
                            }

                            if(!$offer_already_exists) {
                                $coupon->addOffer($offer);
                            }
                        }
                    }
                }

                $em->persist($coupon);
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
     * @Route("/coupon/{stripe_id}/delete")
     * @Template()
     */
    public function deleteCouponAction(Request $request, $stripe_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/coupon/{stripe_id}/delete"
    	);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();

    	// Get the coupon
        $coupon = $em->getRepository('StrimeAPIUserBundle:Coupon')->findOneBy(array('stripe_id' => $stripe_id));

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
        elseif(!is_object($coupon) || ($coupon == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This coupon ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Remove the coupon from the users who have used it.

            // Get the list of users who have used this coupon.
            $query = $em->createQueryBuilder();
            $query->select( 'api_user' );
            $query->from( 'StrimeAPIUserBundle:User','api_user' );
            $query->join( 'api_user.coupons','coupons');
            $query->where('coupons.id = :couponId');
            $query->setParameter("couponId", $coupon->getStripeId());
            $users = $query->getQuery()->getResult();

            // Remove the coupon from these users.
            foreach ($users as $user) {
                
                $user->removeCoupon($coupon);
                $em->persist($user);
            }

            // We delete the coupon
            try {
            	$em->remove($coupon);
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
