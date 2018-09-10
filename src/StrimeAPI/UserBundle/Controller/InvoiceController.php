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

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\UserBundle\Entity\Invoice;

class InvoiceController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/invoices/get")
     * @Template()
     */
    public function getInvoicesAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'), 
            "version" => $this->container->getParameter('app_version'), 
            "method" => "/invoices/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the invoices
        $invoices_results = $em->getRepository('StrimeAPIUserBundle:Invoice')->findAll();
        $invoices = array();

        // If no offer has been created yet.
        if($invoices_results == NULL) {
            $invoices = "No invoice has been found.";
            $json["results"] = $invoices;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($invoices_results as $invoice) {

            // Set the user details
            if($invoice->getUser() != NULL) {
                $user_details = array(
                    "user_id" => $invoice->getUser()->getSecretId(),
                    "first_name" => $invoice->getUser()->getFirstName(),
                    "last_name" => $invoice->getUser()->getLastName(),
                    "email" => $invoice->getUser()->getEmail()
                );
            }
            else {
                $user_details = NULL;
            }

            $invoices[] = array(
                "invoice_id" => $invoice->getInvoiceId(),
                "total_amount" => $invoice->getTotalAmount(),
                "amount_wo_taxes" => $invoice->getAmountWoTaxes(),
                "taxes" => $invoice->getTaxes(),
                "tax_rate" => $invoice->getTaxRate(),
                "day" => $invoice->getDay(),
                "month" => $invoice->getMonth(),
                "year" => $invoice->getYear(),
                "plan_start_date" => $invoice->getPlanStartDate(),
                "plan_end_date" => $invoice->getPlanEndDate(),
                "deleted_user_id" => $invoice->getDeletedUserId(),
                "user_name" => $invoice->getUserName(),
                "user" => $user_details,
                "created_at" => $invoice->getCreatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $invoices;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }



    /**
     * @Route("/invoices/{user_id}/get")
     * @Template()
     */
    public function getInvoicesByUserAction(Request $request, $user_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/invoices/{user_id}/get"
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

        // Get the invoices
		$invoices_results = $em->getRepository('StrimeAPIUserBundle:Invoice')->findByUser($user);
		$invoices = array();

		// If no offer has been created yet.
		if($invoices_results == NULL) {
			$invoices = "No invoice has been found for this user.";
			$json["results"] = $invoices;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($invoices_results as $invoice) {
			$invoices[] = array(
				"invoice_id" => $invoice->getInvoiceId(),
                "total_amount" => $invoice->getTotalAmount(),
                "amount_wo_taxes" => $invoice->getAmountWoTaxes(),
                "taxes" => $invoice->getTaxes(),
                "tax_rate" => $invoice->getTaxRate(),
                "day" => $invoice->getDay(),
                "month" => $invoice->getMonth(),
                "year" => $invoice->getYear(),
                "plan_start_date" => $invoice->getPlanStartDate(),
                "plan_end_date" => $invoice->getPlanEndDate(),
                "deleted_user_id" => $invoice->getDeletedUserId(),
                "user_name" => $invoice->getUserName(),
				"user" => array(
					"user_id" => $user->getSecretId(),
					"first_name" => $user->getFirstName(),
					"last_name" => $user->getLastName(),
					"email" => $user->getEmail()
				),
				"created_at" => $invoice->getCreatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $invoices;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    
    /**
     * @Route("/invoices/get/period/start/{start}/stop/{stop}")
     * @Template()
     */
    public function getInvoicesOnPeriodAction(Request $request, $start, $stop)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'), 
            "version" => $this->container->getParameter('app_version'), 
            "method" => "/invoices/get/period/start/{$start}/stop/{$stop}"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the invoices
        $invoices_results = $em->getRepository('StrimeAPIUserBundle:Invoice')->findAll();
        $invoices = array();

        // If no offer has been created yet.
        if($invoices_results == NULL) {
            $invoices = "No invoice has been found for this period.";
            $json["results"] = $invoices;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Only keep the required invoices
        if($invoices_results != NULL) {
            foreach ($invoices_results as $invoice) {
                $invoice_date = (int)strtotime( $invoice->getMonth()."/".$invoice->getDay()."/".$invoice->getYear() );

                if(($invoice_date >= (int)$start) && ($invoice_date <= (int)$stop)) {

                    // Set the user details
                    if($invoice->getUser() != NULL) {
                        $user_details = array(
                            "user_id" => $invoice->getUser()->getSecretId(),
                            "first_name" => $invoice->getUser()->getFirstName(),
                            "last_name" => $invoice->getUser()->getLastName(),
                            "email" => $invoice->getUser()->getEmail()
                        );
                    }
                    else {
                        $user_details = NULL;
                    }
                    
                    $invoices[] = array(
                        "invoice_id" => $invoice->getInvoiceId(),
                        "total_amount" => $invoice->getTotalAmount(),
                        "amount_wo_taxes" => $invoice->getAmountWoTaxes(),
                        "taxes" => $invoice->getTaxes(),
                        "tax_rate" => $invoice->getTaxRate(),
                        "day" => $invoice->getDay(),
                        "month" => $invoice->getMonth(),
                        "year" => $invoice->getYear(),
                        "plan_start_date" => $invoice->getPlanStartDate(),
                        "plan_end_date" => $invoice->getPlanEndDate(),
                        "deleted_user_id" => $invoice->getDeletedUserId(),
                        "user_name" => $invoice->getUserName(),
                        "user" => $user_details,
                        "created_at" => $invoice->getCreatedAt()
                    );
                }
            }
        }

        // Add the results to the response
        $json["results"] = $invoices;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/invoice/{invoice_id}/get")
     * @Template()
     */
    public function getInvoiceAction(Request $request, $invoice_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'), 
            "version" => $this->container->getParameter('app_version'), 
            "method" => "/invoice/{invoice_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $invoice_details = $em->getRepository('StrimeAPIUserBundle:Invoice')->findOneBy(array('invoice_id' => $invoice_id));
        $invoice = array();

        // If we get a result
        if($invoice_details != NULL) {

            // Set the user details
            if($invoice_details->getUser() != NULL) {

                // Get the details of the offer subscribed
                $offer_content = array(
                    "offer_id" => $invoice_details->getUser()->getOffer()->getSecretId(),
                    "name" => $invoice_details->getUser()->getOffer()->getName(), 
                    "price" => $invoice_details->getUser()->getOffer()->getPrice(),
                    "storage_allowed" => $invoice_details->getUser()->getOffer()->getStorageAllowed(),
                );


                $user_details = array(
                    "user_id" => $invoice_details->getUser()->getSecretId(),
                    "first_name" => $invoice_details->getUser()->getFirstName(),
                    "last_name" => $invoice_details->getUser()->getLastName(),
                    "email" => $invoice_details->getUser()->getEmail(),
                    "company" => $invoice_details->getUser()->getCompany(),
                    "vat_number" => $invoice_details->getUser()->getVatNumber(),
                    "offer" => $offer_content,
                    "storage_used" => $invoice_details->getUser()->getStorageUsed(),
                    "status" => $invoice_details->getUser()->getStatus(),
                    "role" => $invoice_details->getUser()->getRole(),
                    "avatar" => $invoice_details->getUser()->getAvatar(),
                    "opt_in" => $invoice_details->getUser()->getOptIn(),
                    "created_at" => $invoice_details->getUser()->getCreatedAt(),
                    "updated_at" => $invoice_details->getUser()->getUpdatedAt()
                );


                // Get the details of the address
                $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $invoice_details->getUser()));

                if($address != NULL) {
                    $user_details['address'] = $address->getAddress();
                    $user_details['address_more'] = $address->getAddressMore();
                    $user_details['zip'] = $address->getZip();
                    $user_details['city'] = $address->getCity();
                    $user_details['state'] = $address->getState();
                    $user_details['country'] = $address->getCountry();
                }
                else {
                    $user_details['address'] = NULL;
                    $user_details['address_more'] = NULL;
                    $user_details['zip'] = NULL;
                    $user_details['city'] = NULL;
                    $user_details['state'] = NULL;
                    $user_details['country'] = NULL;
                }
            }
            else {
                $user_details = NULL;
            }

            // Prepare the array containing the results
            $invoice = array(
                "invoice_id" => $invoice_id,
                "total_amount" => $invoice_details->getTotalAmount(),
                "amount_wo_taxes" => $invoice_details->getAmountWoTaxes(),
                "taxes" => $invoice_details->getTaxes(),
                "tax_rate" => $invoice_details->getTaxRate(),
                "day" => $invoice_details->getDay(),
                "month" => $invoice_details->getMonth(),
                "year" => $invoice_details->getYear(),
                "plan_start_date" => $invoice_details->getPlanStartDate(),
                "plan_end_date" => $invoice_details->getPlanEndDate(),
                "deleted_user_id" => $invoice_details->getDeletedUserId(),
                "user_name" => $invoice_details->getUserName(),
                "user" => $user_details,
                "created_at" => $invoice_details->getCreatedAt()
            );

            // Add the results to the response
            $json["results"] = $invoice;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $invoice = "No invoice has been found with this ID.";
            $json["results"] = $invoice;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/invoice/add")
     * @Template()
     */
    public function addInvoiceAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/invoice/add"
    	);

    	// Get the data
        $stripe_id = $request->request->get('stripe_id', NULL);
        $user_id = $request->request->get('user_id', NULL);
        $total_amount = (float)$request->request->get('total_amount', NULL);
        $amount_wo_taxes = (float)$request->request->get('amount_wo_taxes', NULL);
        $taxes = (float)$request->request->get('taxes', NULL);
        $tax_rate = (int)$request->request->get('tax_rate', NULL);
    	$day = $request->request->get('day', NULL);
        $month = $request->request->get('month', NULL);
        $year = $request->request->get('year', NULL);
        $plan_start_date = (int)$request->request->get('plan_start_date', NULL);
        $plan_end_date = (int)$request->request->get('plan_end_date', NULL);
        $status = $request->request->get('status', 0);

    	// Get the user details
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // If no user has been found with this user ID
        if($user == NULL) {
        	$json['authorization'] = "No user has been found with this ID.";
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Check if this invoice already exists
        $invoice_exists = $em->getRepository('StrimeAPIUserBundle:Invoice')->findOneBy(array('user' => $user, 'day' => $day, 'month' => $month, 'year' => $year));

        // If the invoice already exists
        if($invoice_exists != NULL) {
        	$json['authorization'] = "This invoice has already been saved.";
            $response = new JsonResponse($json, 409, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If the type of request used is not the one expected.
        elseif(!$request->isMethod('POST')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["error_message"] = "This is not a POST request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If some data are missing
        elseif(($user_id === NULL) || ($total_amount === NULL) || ($amount_wo_taxes === NULL) 
            || ($taxes === NULL) || ($day === NULL) || ($month === NULL) || ($year === NULL)) {

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
            $invoice = new Invoice;

            // We generate the invoice_id
            $highest_invoice_id = $em->createQueryBuilder()
                ->select('MAX(i.id)')
                ->from('StrimeAPIUserBundle:Invoice', 'i')
                ->getQuery()
                ->getSingleScalarResult();
            
            if($highest_invoice_id == NULL)
                $new_invoice_id = 1;
            else
                $new_invoice_id = (int)$highest_invoice_id+1;
            
            if($new_invoice_id < 10)
                $new_invoice_id = "0000".strval($new_invoice_id);
            elseif($new_invoice_id < 100)
                $new_invoice_id = "000".strval($new_invoice_id);
            elseif($new_invoice_id < 1000)
                $new_invoice_id = "00".strval($new_invoice_id);
            elseif($new_invoice_id < 10000)
                $new_invoice_id = "0".strval($new_invoice_id);
            
            $invoice_id = $year.$month.$day.$new_invoice_id;

            // We create the invoice
            try {
                $invoice->setInvoiceId($invoice_id);
                $invoice->setStripeId($stripe_id);
                $invoice->setUser($user);
                $invoice->setTotalAmount($total_amount);
                $invoice->setAmountWoTaxes($amount_wo_taxes);
                $invoice->setTaxes($taxes);
                $invoice->setTaxRate($tax_rate);
                $invoice->setDay($day);
                $invoice->setMonth($month);
                $invoice->setYear($year);
                $invoice->setPlanStartDate($plan_start_date);
                $invoice->setPlanEndDate($plan_end_date);
                $invoice->setStatus($status);

                $em->persist($invoice);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["invoice_id"] = $invoice_id;

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
     * @Route("/invoice/{invoice_id}/edit")
     * @Template()
     */
    public function editInvoiceAction(Request $request, $invoice_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'), 
            "version" => $this->container->getParameter('app_version'), 
            "method" => "/invoice/{invoice_id}/edit"
        );

        // Get the data
        $tax_rate = $request->request->get('tax_rate', NULL);
        $plan_start_date = $request->request->get('plan_start_date', NULL);
        $plan_end_date = $request->request->get('plan_end_date', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();

        // Get the invoice
        $invoice = $em->getRepository('StrimeAPIUserBundle:Invoice')->findOneBy(array('invoice_id' => $invoice_id));

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
        elseif(!is_object($invoice) || ($invoice == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This invoice ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the offer
            try {
                if($tax_rate != NULL)
                    $invoice->setTaxRate($tax_rate);
                if($plan_start_date != NULL)
                    $invoice->setPlanStartDate($plan_start_date);
                if($plan_end_date != NULL)
                    $invoice->setPlanEndDate($plan_end_date);


                $em->persist($invoice);
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
     * @Route("/invoice/{invoice_id}/delete")
     * @Template()
     */
    public function deleteInvoiceAction(Request $request, $invoice_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'), 
    		"version" => $this->container->getParameter('app_version'), 
    		"method" => "/invoice/{invoice_id}/delete"
    	);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $invoice = new Invoice;

    	// Get the contact
        $invoice = $em->getRepository('StrimeAPIUserBundle:Invoice')->findOneBy(array('invoice_id' => $invoice_id));

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
        elseif(!is_object($invoice) || ($invoice == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "This invoice ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We delete the invoice
            try {

                // Delete the invoice
            	$em->remove($invoice);
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
