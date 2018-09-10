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
use StrimeAPI\UserBundle\Mailchimp\MailchimpManager;
use StrimeAPI\GlobalBundle\Auth\HeadersAuthorization;
use Aws\S3\S3Client;

use StrimeAPI\UserBundle\Entity\Address;
use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\UserBundle\Entity\EmailToConfirm;
use StrimeAPI\UserBundle\Entity\Invoice;
use StrimeAPI\UserBundle\Entity\Offer;
use StrimeAPI\UserBundle\Entity\Right;
use StrimeAPI\UserBundle\Entity\Token;
use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\UserBundle\Entity\UserFacebook;
use StrimeAPI\UserBundle\Entity\UserGoogle;
use StrimeAPI\UserBundle\Entity\UserSlack;
use StrimeAPI\UserBundle\Entity\UserYoutube;
use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\VideoBundle\Entity\EncodingJob;
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\VideoBundle\Entity\Video;

class UserController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/users/get")
     * @Template()
     */
    public function getUsersAction(Request $request)
    {

        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/users/get"
        );


    	// Set Doctrine Manager and create the object and get all the users
        $em = $this->getDoctrine()->getManager();
        $users = new User;
        $users_results = $em->getRepository('StrimeAPIUserBundle:User')->findAll();
		$users = array();

		// If we get a result
		if($users_results != NULL) {

			foreach ($users_results as $user) {

				// Get the details of the offer subscribed
				$offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('id' => $user->getOffer()));

                // Get the details of the address
                $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user));

				// Prepare the offer content
				if($offer != NULL) {
					$offer_content = array(
						"offer_id" => $offer->getSecretId(),
						"name" => $offer->getName(),
						"price" => $offer->getPrice()
					);
				}
				else {
					$offer_content = array();
				}

                // Prepare the list of rights
                $user_helper = $this->container->get('strime_api.helpers.user_helper');
                $user_helper->user = $user;
                $rights_list = $user_helper->setUserRights();

                // Check if he needs to confirm his email address
                $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user));

                if($needs_to_confirm_email != NULL)
                    $needs_to_confirm_email = TRUE;
                else
                    $needs_to_confirm_email = FALSE;

                // Check if the user has a connection with Google
                $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_google != NULL) {
                    $user_google_details = array(
                        "google_id" => $user_google->getGoogleId(),
                        "google_image" => $user_google->getGoogleImage()
                    );
                }
                else {
                    $user_google_details = NULL;
                }

                // Check if the user has a connection with Youtube
                $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_youtube != NULL) {
                    $user_youtube_details = array(
                        "youtube_id" => $user_youtube->getYoutubeId()
                    );
                }
                else {
                    $user_youtube_details = NULL;
                }

                // Check if the user has a connection with Facebook
                $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_facebook != NULL) {
                    $user_facebook_details = array(
                        "facebook_id" => $user_facebook->getFacebookId(),
                        "facebook_image" => $user_facebook->getFacebookImage()
                    );
                }
                else {
                    $user_facebook_details = NULL;
                }

                // Check if the user has a connection with Slack
                $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_slack != NULL) {
                    $user_slack_details = array(
                        "webhook_url" => $user_slack->getWebhookUrl()
                    );
                }
                else {
                    $user_slack_details = NULL;
                }

                // Set the avatar
                $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
                $user_avatar = $avatar_helper->setUserAvatar($user, $user_google_details, $user_facebook_details);

				// Set the user in the results
				$user_data = array(
					"user_id" => $user->getSecretId(),
					"email" => $user->getEmail(),
					"first_name" => $user->getFirstName(),
					"last_name" => $user->getLastName(),
					"company" => $user->getCompany(),
                    "vat_number" => $user->getVatNumber(),
					"offer" => $offer_content,
                    "rights" => $rights_list,
					"storage_used" => $user->getStorageUsed(),
					"status" => $user->getStatus(),
                    "role" => $user->getRole(),
                    "avatar" => $user_avatar,
					"opt_in" => $user->getOptIn(),
                    "mail_notification" => $user->getMailNotification(),
                    "last_login" => $user->getLastLogin(),
                    "locale" => $user->getLocale(),
                    "needs_to_confirm_email" => $needs_to_confirm_email,
                    "user_google_details" => $user_google_details,
                    "user_youtube_details" => $user_youtube_details,
                    "user_facebook_details" => $user_facebook_details,
                    "user_slack_details" => $user_slack_details,
					"created_at" => $user->getCreatedAt(),
					"updated_at" => $user->getUpdatedAt()
				);

                if($address != NULL) {
                    $user_data['address'] = $address->getAddress();
                    $user_data['address_more'] = $address->getAddressMore();
                    $user_data['zip'] = $address->getZip();
                    $user_data['city'] = $address->getCity();
                    $user_data['state'] = $address->getState();
                    $user_data['country'] = $address->getCountry();
                }
                else {
                    $user_data['address'] = NULL;
                    $user_data['address_more'] = NULL;
                    $user_data['zip'] = NULL;
                    $user_data['city'] = NULL;
                    $user_data['state'] = NULL;
                    $user_data['country'] = NULL;
                }

                $users[] = $user_data;

		        // Add the results to the response
		        $json["results"] = $users;
        		$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
			}
		}

		// If no user has been created yet.
		else {
			$json["message"] = "No user has been created yet.";
			$json["results"] = array();
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

        // Return the results
        return $response;
    }


    /**
     * @Route("/users/get/last/{nb_users}")
     * @Template()
     */
    public function getLastUsersAction(Request $request, $nb_users)
    {

        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/users/get/last/{nb_users}"
        );


        // Set Doctrine Manager and create the object and get the users
        $em = $this->getDoctrine()->getManager();
        $users = new User;
        $users_results = $em->getRepository('StrimeAPIUserBundle:User')->findBy(array(), array('created_at' => 'DESC'), $nb_users);
        $users = array();

        // If we get a result
        if($users_results != NULL) {

            foreach ($users_results as $user) {

                // Get the details of the offer subscribed
                $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('id' => $user->getOffer()));

                // Get the details of the address
                $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user));

                // Prepare the offer content
                if($offer != NULL) {
                    $offer_content = array(
                        "offer_id" => $offer->getSecretId(),
                        "name" => $offer->getName(),
                        "price" => $offer->getPrice()
                    );
                }
                else {
                    $offer_content = array();
                }

                // Prepare the list of rights
                $user_helper = $this->container->get('strime_api.helpers.user_helper');
                $user_helper->user = $user;
                $rights_list = $user_helper->setUserRights();

                // Check if he needs to confirm his email address
                $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user));

                if($needs_to_confirm_email != NULL)
                    $needs_to_confirm_email = TRUE;
                else
                    $needs_to_confirm_email = FALSE;

                // Check if the user has a connection with Google
                $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_google != NULL) {
                    $user_google_details = array(
                        "google_id" => $user_google->getGoogleId(),
                        "google_image" => $user_google->getGoogleImage()
                    );
                }
                else {
                    $user_google_details = NULL;
                }

                // Check if the user has a connection with Youtube
                $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_youtube != NULL) {
                    $user_youtube_details = array(
                        "youtube_id" => $user_youtube->getYoutubeId()
                    );
                }
                else {
                    $user_youtube_details = NULL;
                }

                // Check if the user has a connection with Facebook
                $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_facebook != NULL) {
                    $user_facebook_details = array(
                        "facebook_id" => $user_facebook->getFacebookId(),
                        "facebook_image" => $user_facebook->getFacebookImage()
                    );
                }
                else {
                    $user_facebook_details = NULL;
                }

                // Check if the user has a connection with Slack
                $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_slack != NULL) {
                    $user_slack_details = array(
                        "webhook_url" => $user_slack->getWebhookUrl()
                    );
                }
                else {
                    $user_slack_details = NULL;
                }

                // Set the avatar
                $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
                $user_avatar = $avatar_helper->setUserAvatar($user, $user_google_details, $user_facebook_details);

                // Set the user in the results
                $user_data = array(
                    "user_id" => $user->getSecretId(),
                    "email" => $user->getEmail(),
                    "first_name" => $user->getFirstName(),
                    "last_name" => $user->getLastName(),
                    "company" => $user->getCompany(),
                    "vat_number" => $user->getVatNumber(),
                    "offer" => $offer_content,
                    "rights" => $rights_list,
                    "storage_used" => $user->getStorageUsed(),
                    "status" => $user->getStatus(),
                    "role" => $user->getRole(),
                    "avatar" => $user_avatar,
                    "opt_in" => $user->getOptIn(),
                    "mail_notification" => $user->getMailNotification(),
                    "last_login" => $user->getLastLogin(),
                    "locale" => $user->getLocale(),
                    "needs_to_confirm_email" => $needs_to_confirm_email,
                    "user_google_details" => $user_google_details,
                    "user_youtube_details" => $user_youtube_details,
                    "user_facebook_details" => $user_facebook_details,
                    "user_slack_details" => $user_slack_details,
                    "created_at" => $user->getCreatedAt(),
                    "updated_at" => $user->getUpdatedAt()
                );

                if($address != NULL) {
                    $user_data['address'] = $address->getAddress();
                    $user_data['address_more'] = $address->getAddressMore();
                    $user_data['zip'] = $address->getZip();
                    $user_data['city'] = $address->getCity();
                    $user_data['state'] = $address->getState();
                    $user_data['country'] = $address->getCountry();
                }
                else {
                    $user_data['address'] = NULL;
                    $user_data['address_more'] = NULL;
                    $user_data['zip'] = NULL;
                    $user_data['city'] = NULL;
                    $user_data['state'] = NULL;
                    $user_data['country'] = NULL;
                }

                $users[] = $user_data;

                // Add the results to the response
                $json["results"] = $users;
                $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // If no user has been created yet.
        else {
            $json["message"] = "No user has been created yet.";
            $json["results"] = array();
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/get")
     * @Template()
     */
    public function getUserAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/get"
    	);

    	// Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
		$user_details = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));
		$user = array();

		// If we get a result
		if($user_details != NULL) {

			// Get the details of the offer subscribed
			$offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('id' => $user_details->getOffer()));

            // Get the details of the address
            $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user_details));

			// Prepare the offer content
			if($offer != NULL) {
				$offer_content = array(
					"offer_id" => $offer->getSecretId(),
					"name" => $offer->getName(),
					"price" => $offer->getPrice(),
                    "storage_allowed" => $offer->getStorageAllowed(),
				);
			}
			else {
				$offer_content = array();
			}

            // Prepare the list of rights
            $user_helper = $this->container->get('strime_api.helpers.user_helper');
            $user_helper->user = $user_details;
            $rights_list = $user_helper->setUserRights();

            // Prepare the coupons content
            $coupons_list = array();

            if($user_details->getCoupons() != NULL) {
                $coupons = $user_details->getCoupons();

                foreach ($coupons as $coupon) {

                    $coupons_list[] = $coupon->getStripeId();
                }
            }

            // Check if he needs to confirm his email address
            $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user_details));

            if($needs_to_confirm_email != NULL)
                $needs_to_confirm_email = TRUE;
            else
                $needs_to_confirm_email = FALSE;

            // Check if the user has a connection with Google
            $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_google != NULL) {
                $user_google_details = array(
                    "google_id" => $user_google->getGoogleId(),
                    "google_image" => $user_google->getGoogleImage()
                );
            }
            else {
                $user_google_details = NULL;
            }

            // Check if the user has a connection with Youtube
            $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_youtube != NULL) {
                $user_youtube_details = array(
                    "youtube_id" => $user_youtube->getYoutubeId()
                );
            }
            else {
                $user_youtube_details = NULL;
            }

            // Check if the user has a connection with Facebook
            $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_facebook != NULL) {
                $user_facebook_details = array(
                    "facebook_id" => $user_facebook->getFacebookId(),
                    "facebook_image" => $user_facebook->getFacebookImage()
                );
            }
            else {
                $user_facebook_details = NULL;
            }

            // Check if the user has a connection with Slack
            $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_slack != NULL) {
                $user_slack_details = array(
                    "webhook_url" => $user_slack->getWebhookUrl()
                );
            }
            else {
                $user_slack_details = NULL;
            }

            // Set the avatar
            $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
            $user_avatar = $avatar_helper->setUserAvatar($user_details, $user_google_details, $user_facebook_details);

			// Prepare the array containing the results
			$user = array(
				"user_id" => $user_details->getSecretId(),
                "stripe_id" => $user_details->getStripeId(),
                "stripe_sub_id" => $user_details->getStripeSubId(),
				"email" => $user_details->getEmail(),
				"first_name" => $user_details->getFirstName(),
				"last_name" => $user_details->getLastName(),
				"company" => $user_details->getCompany(),
                "vat_number" => $user_details->getVatNumber(),
				"offer" => $offer_content,
                "rights" => $rights_list,
                "coupons" => $coupons_list,
				"storage_used" => $user_details->getStorageUsed(),
				"status" => $user_details->getStatus(),
                "role" => $user_details->getRole(),
                "avatar" => $user_avatar,
				"opt_in" => $user_details->getOptIn(),
                "mail_notification" => $user_details->getMailNotification(),
                "last_login" => $user_details->getLastLogin(),
                "locale" => $user_details->getLocale(),
                "needs_to_confirm_email" => $needs_to_confirm_email,
                "user_google_details" => $user_google_details,
                "user_youtube_details" => $user_youtube_details,
                "user_facebook_details" => $user_facebook_details,
                "user_slack_details" => $user_slack_details,
				"created_at" => $user_details->getCreatedAt(),
				"updated_at" => $user_details->getUpdatedAt()
			);

            if($address != NULL) {
                $user['address'] = $address->getAddress();
                $user['address_more'] = $address->getAddressMore();
                $user['zip'] = $address->getZip();
                $user['city'] = $address->getCity();
                $user['state'] = $address->getState();
                $user['country'] = $address->getCountry();
            }
            else {
                $user['address'] = NULL;
                $user['address_more'] = NULL;
                $user['zip'] = NULL;
                $user['city'] = NULL;
                $user['state'] = NULL;
                $user['country'] = NULL;
            }

	        // Add the results to the response
	        $json["results"] = $user;
        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

		// If there is no user with this ID
		else  {
			$user = "No user has been found with this ID.";
			$json["results"] = $user;
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
		}

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{email}/get-by-email")
     * @Template()
     */
    public function getUserByEmailAction(Request $request, $email)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/user/{email}/get-by-email"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $user_details = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('email' => $email));
        $user = array();

        // If we get a result
        if($user_details != NULL) {

            // Get the details of the offer subscribed
            $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('id' => $user_details->getOffer()));

            // Get the details of the address
            $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user_details));

            // Prepare the offer content
            if($offer != NULL) {
                $offer_content = array(
                    "offer_id" => $offer->getSecretId(),
                    "name" => $offer->getName(),
                    "price" => $offer->getPrice(),
                    "storage_allowed" => $offer->getStorageAllowed(),
                );
            }
            else {
                $offer_content = array();
            }

            // Prepare the list of rights
            $user_helper = $this->container->get('strime_api.helpers.user_helper');
            $user_helper->user = $user_details;
            $rights_list = $user_helper->setUserRights();

            // Prepare the coupons content
            $coupons_list = array();

            if($user_details->getCoupons() != NULL) {
                $coupons = $user_details->getCoupons();

                foreach ($coupons as $coupon) {

                    $coupons_list[] = $coupon->getStripeId();
                }
            }

            // Check if he needs to confirm his email address
            $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user_details));

            if($needs_to_confirm_email != NULL)
                $needs_to_confirm_email = TRUE;
            else
                $needs_to_confirm_email = FALSE;

            // Check if the user has a connection with Google
            $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_google != NULL) {
                $user_google_details = array(
                    "google_id" => $user_google->getGoogleId(),
                    "google_image" => $user_google->getGoogleImage()
                );
            }
            else {
                $user_google_details = NULL;
            }

            // Check if the user has a connection with Youtube
            $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

            // Set the variable with the result
            if($user_youtube != NULL) {
                $user_youtube_details = array(
                    "youtube_id" => $user_youtube->getYoutubeId()
                );
            }
            else {
                $user_youtube_details = NULL;
            }

            // Check if the user has a connection with Facebook
            $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_facebook != NULL) {
                $user_facebook_details = array(
                    "facebook_id" => $user_facebook->getFacebookId(),
                    "facebook_image" => $user_facebook->getFacebookImage()
                );
            }
            else {
                $user_facebook_details = NULL;
            }

            // Check if the user has a connection with Slack
            $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_slack != NULL) {
                $user_slack_details = array(
                    "webhook_url" => $user_slack->getWebhookUrl()
                );
            }
            else {
                $user_slack_details = NULL;
            }

            // Set the avatar
            $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
            $user_avatar = $avatar_helper->setUserAvatar($user_details, $user_google_details, $user_facebook_details);

            // Prepare the array containing the results
            $user = array(
                "user_id" => $user_details->getSecretId(),
                "stripe_id" => $user_details->getStripeId(),
                "stripe_sub_id" => $user_details->getStripeSubId(),
                "email" => $user_details->getEmail(),
                "first_name" => $user_details->getFirstName(),
                "last_name" => $user_details->getLastName(),
                "company" => $user_details->getCompany(),
                "vat_number" => $user_details->getVatNumber(),
                "offer" => $offer_content,
                "rights" => $rights_list,
                "coupons" => $coupons_list,
                "storage_used" => $user_details->getStorageUsed(),
                "status" => $user_details->getStatus(),
                "role" => $user_details->getRole(),
                "avatar" => $user_avatar,
                "opt_in" => $user_details->getOptIn(),
                "mail_notification" => $user_details->getMailNotification(),
                "last_login" => $user_details->getLastLogin(),
                "locale" => $user_details->getLocale(),
                "needs_to_confirm_email" => $needs_to_confirm_email,
                "user_google_details" => $user_google_details,
                "user_youtube_details" => $user_youtube_details,
                "user_facebook_details" => $user_facebook_details,
                "user_slack_details" => $user_slack_details,
                "created_at" => $user_details->getCreatedAt(),
                "updated_at" => $user_details->getUpdatedAt()
            );

            if($address != NULL) {
                $user['address'] = $address->getAddress();
                $user['address_more'] = $address->getAddressMore();
                $user['zip'] = $address->getZip();
                $user['city'] = $address->getCity();
                $user['state'] = $address->getState();
                $user['country'] = $address->getCountry();
            }
            else {
                $user['address'] = NULL;
                $user['address_more'] = NULL;
                $user['zip'] = NULL;
                $user['city'] = NULL;
                $user['state'] = NULL;
                $user['country'] = NULL;
            }

            // Add the results to the response
            $json["results"] = $user;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $user = "No user has been found with this ID.";
            $json["results"] = $user;
            $json["error_source"] = "user_doesnt_exist";
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{stripe_id}/get-by-stripe-id")
     * @Template()
     */
    public function getUserByStripeIDAction(Request $request, $stripe_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/user/{stripe_id}/get-by-stripe-id"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $user_details = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('stripe_id' => $stripe_id));
        $user = array();

        // If we get a result
        if($user_details != NULL) {

            // Get the details of the offer subscribed
            $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('id' => $user_details->getOffer()));

            // Get the details of the address
            $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user_details));

            // Prepare the offer content
            if($offer != NULL) {
                $offer_content = array(
                    "offer_id" => $offer->getSecretId(),
                    "name" => $offer->getName(),
                    "price" => $offer->getPrice(),
                    "storage_allowed" => $offer->getStorageAllowed(),
                );
            }
            else {
                $offer_content = array();
            }

            // Prepare the list of rights
            $user_helper = $this->container->get('strime_api.helpers.user_helper');
            $user_helper->user = $user_details;
            $rights_list = $user_helper->setUserRights();

            // Prepare the coupons content
            $coupons_list = array();

            if($user_details->getCoupons() != NULL) {
                $coupons = $user_details->getCoupons();

                foreach ($coupons as $coupon) {

                    $coupons_list[] = $coupon->getStripeId();
                }
            }

            // Check if he needs to confirm his email address
            $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user_details));

            if($needs_to_confirm_email != NULL)
                $needs_to_confirm_email = TRUE;
            else
                $needs_to_confirm_email = FALSE;

            // Check if the user has a connection with Google
            $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user_details));

            if($user_google != NULL) {
                $user_google_details = array(
                    "google_id" => $user_google->getGoogleId(),
                    "google_image" => $user_google->getGoogleImage()
                );
            }
            else {
                $user_google_details = NULL;
            }

            // Check if the user has a connection with Youtube
            $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

            // Set the variable with the result
            if($user_youtube != NULL) {
                $user_youtube_details = array(
                    "youtube_id" => $user_youtube->getYoutubeId()
                );
            }
            else {
                $user_youtube_details = NULL;
            }

            // Check if the user has a connection with Facebook
            $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user_details));

            if($user_facebook != NULL) {
                $user_facebook_details = array(
                    "facebook_id" => $user_facebook->getFacebookId(),
                    "facebook_image" => $user_facebook->getFacebookImage()
                );
            }
            else {
                $user_facebook_details = NULL;
            }

            // Check if the user has a connection with Slack
            $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user_details));

            // Set the variable with the result
            if($user_slack != NULL) {
                $user_slack_details = array(
                    "webhook_url" => $user_slack->getWebhookUrl()
                );
            }
            else {
                $user_slack_details = NULL;
            }

            // Set the avatar
            $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
            $user_avatar = $avatar_helper->setUserAvatar($user_details, $user_google_details, $user_facebook_details);

            // Prepare the array containing the results
            $user = array(
                "user_id" => $user_details->getSecretId(),
                "stripe_id" => $user_details->getStripeId(),
                "stripe_sub_id" => $user_details->getStripeSubId(),
                "email" => $user_details->getEmail(),
                "first_name" => $user_details->getFirstName(),
                "last_name" => $user_details->getLastName(),
                "company" => $user_details->getCompany(),
                "vat_number" => $user_details->getVatNumber(),
                "offer" => $offer_content,
                "rights" => $rights_list,
                "coupons" => $coupons_list,
                "storage_used" => $user_details->getStorageUsed(),
                "status" => $user_details->getStatus(),
                "role" => $user_details->getRole(),
                "avatar" => $user_avatar,
                "opt_in" => $user_details->getOptIn(),
                "mail_notification" => $user_details->getMailNotification(),
                "last_login" => $user_details->getLastLogin(),
                "locale" => $user_details->getLocale(),
                "needs_to_confirm_email" => $needs_to_confirm_email,
                "user_google_details" => $user_google_details,
                "user_youtube_details" => $user_youtube_details,
                "user_facebook_details" => $user_facebook_details,
                "user_slack_details" => $user_slack_details,
                "created_at" => $user_details->getCreatedAt(),
                "updated_at" => $user_details->getUpdatedAt()
            );

            if($address != NULL) {
                $user['address'] = $address->getAddress();
                $user['address_more'] = $address->getAddressMore();
                $user['zip'] = $address->getZip();
                $user['city'] = $address->getCity();
                $user['state'] = $address->getState();
                $user['country'] = $address->getCountry();
            }
            else {
                $user['address'] = NULL;
                $user['address_more'] = NULL;
                $user['zip'] = NULL;
                $user['city'] = NULL;
                $user['state'] = NULL;
                $user['country'] = NULL;
            }

            // Add the results to the response
            $json["results"] = $user;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $user = "No user has been found with this ID.";
            $json["results"] = $user;
            $json["error_source"] = "user_doesnt_exist";
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/get-token")
     * @Template()
     */
    public function getTokenAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/user/{user_id}/get-token"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $user_details = new User;

        // Get the user details
        $user_details = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

        // If we get a result
        if($user_details != NULL) {

            // Check if there is a token for this user
            $token = new Token;
            $token = $em->getRepository('StrimeAPIUserBundle:Token')->findOneBy(array('user' => $user_details->getId()));

            // Prepare the token content
            if($token != NULL) {
                $token_details = array(
                    "token" => $token->getToken(),
                    "created_at" => $token->getCreatedAt()
                );
            }
            else {
                $json["message"] = "No token has been found for this user.";
                $json["results"] = array();
                $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // Add the results to the response
            $json["results"] = $token_details;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $user = "No user has been found with this ID.";
            $json["results"] = $user;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/add")
     * @Template()
     */
    public function addUserAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/add"
    	);

    	// Get the data
        $stripe_id = $request->request->get('stripe_id', NULL);
        $stripe_sub_id = $request->request->get('stripe_sub_id', NULL);
    	$email = $request->request->get('email', NULL);
    	$password = $request->request->get('password', NULL);
    	$first_name = $request->request->get('first_name', NULL);
    	$last_name = $request->request->get('last_name', NULL);
    	$company = $request->request->get('company', NULL);
    	$address = $request->request->get('address', NULL);
    	$address_more = $request->request->get('address_more', NULL);
    	$zip = $request->request->get('zip', NULL);
    	$state = $request->request->get('state', NULL);
    	$city = $request->request->get('city', NULL);
    	$country = $request->request->get('country', NULL);
        $vat_number = $request->request->get('vat_number', NULL);
    	$offer_id = $request->request->get('offer_id', NULL);
        $coupon_id = $request->request->get('coupon', NULL);
    	$opt_in = $request->request->get('opt_in', 0);
        $mail_notification = $request->request->get('mail_notification', 'now');
        $locale = $request->request->get('locale', NULL);
        $user_google_id = $request->request->get('user_google_id', NULL);
        $user_google_image = $request->request->get('user_google_image', NULL);
        $user_facebook_id = $request->request->get('user_facebook_id', NULL);
        $user_facebook_image = $request->request->get('user_facebook_image', NULL);

        // Change the format of the email
        $email = strtolower($email);

    	// We get the object corresponding to this offer.
    	$em = $this->getDoctrine()->getManager();
    	$offer = new Offer;
    	$offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $offer_id));

    	// We check that the email is unique.
    	$check_email = new User;
    	$check_email = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('email' => $email));

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
        elseif(($email == NULL) || ($password == NULL) || ($offer_id == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "Some data are missing.";
            $json["error_source"] = "missing_data";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If this email is already used
        elseif($check_email != NULL) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This email is already used.";
            $json["error_source"] = "email_already_used";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Prepare the entity
            $user = new User;

            // We generate a secret_id
            $secret_id_exists = TRUE;
		    $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
		        $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));
            }

            // We encrypt the password
            $password_time = time();
            $password = hash('sha512', $password_time.$password.$this->container->getParameter('secret'));

            // Get the details of the coupon
            if($coupon_id != NULL) {
                $coupon = $em->getRepository('StrimeAPIUserBundle:Coupon')->findOneBy(array('stripe_id' => $coupon_id));
            }
            else {
                $coupon = NULL;
            }

            // We create the user
            try {
                $user->setSecretId($secret_id);
                $user->setStripeId($stripe_id);
                $user->setStripeSubId($stripe_sub_id);
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setPasswordTime($password_time);
                $user->setFirstName($first_name);
                $user->setLastName($last_name);
                $user->setCompany($company);
                $user->setVatNumber($vat_number);
                $user->setOffer($offer);
                $user->setOptIn($opt_in);
                $user->setMailNotification($mail_notification);
                $user->setLastLogin( time() );
                $user->setLocale($locale);

                if($coupon != NULL)
                    $user->addCoupon($coupon);

                $em->persist($user);
                $em->flush();

                // Create an entry in the list of emails to confirm
                if(($user_google_id === NULL) && ($user_facebook_id === NULL)) {
                    $email_to_confirm = new EmailToConfirm;
                    $email_to_confirm->setUser( $user );
                    $email_to_confirm->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
                    $em->persist($email_to_confirm);
                    $em->flush();
                }

                // If a Google ID has been passed, create an entry in the GoogleUser table
                if(($user_google_id != NULL) || ($user_google_image != NULL)) {
                    $user_google = new UserGoogle;
                    if($user_google_id != NULL)
                        $user_google->setGoogleId($user_google_id);
                    if($user_google_image != NULL)
                        $user_google->setGoogleImage($user_google_image);
                    $user_google->setUser($user);
                    $em->persist($user_google);
                    $em->flush();
                }

                // If a Facebook ID has been passed, create an entry in the GoogleFacebook table
                if(($user_facebook_id != NULL) || ($user_facebook_image != NULL)) {
                    $user_facebook = new UserFacebook;
                    if($user_facebook_id != NULL)
                        $user_facebook->setFacebookId($user_facebook_id);
                    if($user_facebook_image != NULL)
                        $user_facebook->setFacebookImage($user_facebook_image);
                    $user_facebook->setUser($user);
                    $em->persist($user_facebook);
                    $em->flush();
                }

                // Create an address object
                if(($address != NULL) || ($address_more != NULL) || ($zip != NULL) || ($city != NULL) || ($state != NULL) || ($country != NULL)) {

                    // Prepare the entity
                    $address_object = new Address;
                    $address_object->setUser($user);
                    $address_object->setAddress($address);
                    $address_object->setAddressMore($address_more);
                    $address_object->setZip($zip);
                    $address_object->setCity($city);
                    $address_object->setState($state);
                    $address_object->setCountry($country);

                    $em->persist($address_object);
                    $em->flush();
                }

                // If the user opted in, add him to the Mailchimp List
                if($opt_in == 1) {
                	$mailchimp_manager = new MailchimpManager();
	                $lists = json_decode( $mailchimp_manager->getLists() );

	                // We loop through the lists to make sure that the list of the clients exists.
	                foreach ($lists->{"lists"} as $list) {

	                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

	                		// Set the mailchimp parameters
	                		$mailchimp_manager->email = $email;
                            $mailchimp_manager->locale = strtoupper( $locale );
	                		$mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');

	                		if($first_name != NULL)
	                			$mailchimp_manager->first_name = $first_name;
	                		if($last_name != NULL)
	                			$mailchimp_manager->last_name = $last_name;

	                		// Trigger the subscription
	                		$subscription = $mailchimp_manager->subscribeMember();

	                		// Return the result of the subscription
	                		$json["mailchimp_subscription"] = json_decode( $subscription );
	                	}
	                }
                }

                // Create a new project to his account
                $project = new Project;

                // We generate a secret_id
                $project_secret_id_exists = TRUE;
                while($project_secret_id_exists != NULL) {
                    $project_secret_id = $token_generator->generateToken(10);
                    $project_secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $project_secret_id));
                }

                // We create the project
                $project->setSecretId($project_secret_id);
                $project->setUser($user);
                if($locale != NULL)
                    $project->setName( $this->container->getParameter('default_project_title_' . $locale) );
                else
                    $project->setName( $this->container->getParameter('default_project_title_en') );

                $em->persist($project);
                $em->flush();


                // Add the demo videos to his account
                $video1 = new Video;
                $video2 = new Video;
                $uploads_path = realpath( __DIR__.'/../../../../web/demo/' ) . '/';

                $video1_file = $uploads_path . 'demo1.mp4';
                $video1_file_converted_mp4 = $uploads_path . 'demo1-converted.mp4';
                $video1_file_converted_webm = $uploads_path . 'demo1-converted.webm';
                $video1_file_converted_cover = $uploads_path . 'demo1-converted.jpg';
                $video2_file = $uploads_path . 'demo2.mp4';
                $video2_file_converted_mp4 = $uploads_path . 'demo2-converted.mp4';
                $video2_file_converted_webm = $uploads_path . 'demo2-converted.webm';
                $video2_file_converted_cover = $uploads_path . 'demo2-converted.jpg';

                // We generate a secret_id for the first video
                $video1_secret_id_exists = TRUE;
                while($video1_secret_id_exists != NULL) {
                    $video1_secret_id = $token_generator->generateToken(10);
                    $video1_secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video1_secret_id));
                }

                // We generate a secret_id for the second video
                $video2_secret_id_exists = TRUE;
                while($video2_secret_id_exists != NULL) {
                    $video2_secret_id = $token_generator->generateToken(10);
                    $video2_secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video2_secret_id));
                }

                // We create the videos
                $video1->setSecretId($video1_secret_id);
                $video1->setUser($user);
                $video1->setProject($project);
                if($locale != NULL)
                    $video1->setName( $this->container->getParameter('default_video_1_title_' . $locale) );
                else
                    $video1->setName( $this->container->getParameter('default_video_1_title_en') );

                $video2->setSecretId($video2_secret_id);
                $video2->setUser($user);
                $video2->setProject($project);
                if($locale != NULL)
                    $video2->setName( $this->container->getParameter('default_video_2_title_' . $locale) );
                else
                    $video2->setName( $this->container->getParameter('default_video_2_title_en') );

                $em->persist($video1);
                $em->persist($video2);
                $em->flush();

                // Create demo comments for the first demo video
                for($i = 1; $i < 3; $i++) {
                    $comment = new Comment;

                    // We generate a secret_id
                    $comment_secret_id_exists = TRUE;
                    while($comment_secret_id_exists != NULL) {
                        $comment_secret_id = $token_generator->generateToken(10);
                        $comment_secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $comment_secret_id));
                    }

                    // Get the parameters
                    $comment_parameters = $this->container->getParameter('default_content_comment_' . $i);

                    // We create the comment
                    $comment->setSecretId( $comment_secret_id );
                    $comment->setUser( $user );
                    $comment->setTime( $comment_parameters["time"] );
                    $comment->setArea( $comment_parameters["area"] );
                    $comment->setVideo($video1);
                    if($locale != NULL)
                        $comment->setComment( $comment_parameters["comment_" . $locale] );
                    else
                        $comment->setComment( $comment_parameters["comment_en"] );

                    $em->persist($comment);
                    $em->flush();
                }

                // Create demo comments for the second demo video
                for($i = 3; $i < 6; $i++) {
                    $comment = new Comment;

                    // We generate a secret_id
                    $comment_secret_id_exists = TRUE;
                    while($comment_secret_id_exists != NULL) {
                        $comment_secret_id = $token_generator->generateToken(10);
                        $comment_secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $comment_secret_id));
                    }

                    // Get the parameters
                    $comment_parameters = $this->container->getParameter('default_content_comment_' . $i);

                    // We create the comment
                    $comment->setSecretId( $comment_secret_id );
                    $comment->setUser( $user );
                    $comment->setTime( $comment_parameters["time"] );
                    $comment->setArea( $comment_parameters["area"] );
                    $comment->setVideo($video2);
                    if($locale != NULL)
                        $comment->setComment( $comment_parameters["comment_" . $locale] );
                    else
                        $comment->setComment( $comment_parameters["comment_en"] );

                    $em->persist($comment);
                    $em->flush();
                }

                // Instantiate the S3 client using your credential profile
                $aws = S3Client::factory(array(
                    'credentials' => array(
                        'key'       => $this->container->getParameter('aws_key'),
                        'secret'    => $this->container->getParameter('aws_secret')
                    ),
                    'version' => 'latest',
                    'region' => $this->container->getParameter('aws_region')
                ));

                // Get client instances from the service locator by name
                // $s3Client = $aws->get('s3');

                // Get the buckets list
                $buckets_list = $aws->listBuckets();

                // Generate the bucket folder
                $bucket_folder = $secret_id."/";
                $bucket_folder .= $project_secret_id."/";


                // Send the file to Amazon S3
                foreach ($buckets_list['Buckets'] as $bucket) {

                    if(strcmp($bucket['Name'], $this->container->getParameter('aws_bucket')) == 0) {

                        // Upload the file to S3
                        $s3_upload_demo1 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder."demo1.mp4",
                            'SourceFile' => $video1_file
                        ));

                        // Upload the webm video
                        $s3_upload_webm_demo1 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder.'demo1-converted.webm',
                            'SourceFile' => $video1_file_converted_webm
                        ));

                        // Upload the x264 video
                        $s3_upload_x264_demo1 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder.'demo1-converted.mp4',
                            'SourceFile' => $video1_file_converted_mp4
                        ));

                        // Upload the jpg screenshot
                        $s3_upload_screenshot_demo1 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder.'demo1-converted.jpg',
                            'SourceFile' => $video1_file_converted_cover
                        ));

                        // Upload the file to S3
                        $s3_upload_demo2 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder."demo2.mp4",
                            'SourceFile' => $video2_file
                        ));

                        // Upload the webm video
                        $s3_upload_webm_demo2 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder.'demo2-converted.webm',
                            'SourceFile' => $video2_file_converted_webm
                        ));

                        // Upload the x264 video
                        $s3_upload_x264_demo2 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder.'demo2-converted.mp4',
                            'SourceFile' => $video2_file_converted_mp4
                        ));

                        // Upload the jpg screenshot
                        $s3_upload_screenshot_demo2 = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder.'demo2-converted.jpg',
                            'SourceFile' => $video2_file_converted_cover
                        ));
                    }
                }

                // If the upload occured properly
                if(($s3_upload_demo1 != NULL) && ($s3_upload_webm_demo1 != NULL) && ($s3_upload_x264_demo1 != NULL) && ($s3_upload_screenshot_demo1 != NULL)
                    && ($s3_upload_demo2 != NULL) && ($s3_upload_webm_demo2 != NULL) && ($s3_upload_x264_demo2 != NULL) && ($s3_upload_screenshot_demo2 != NULL)) {

                    // Get the URL of the file on Amazon S3
                    $s3_https_url_demo1 = $s3_upload_demo1['ObjectURL'];
                    $s3_https_url_screenshot_demo1 = $s3_upload_screenshot_demo1['ObjectURL'];
                    $file_name_with_ext = basename( $s3_https_url_demo1 );
                    $file_name_elts = explode('.', $file_name_with_ext);
                    $file_name_without_ext = $file_name_elts[0];
                    $video_demo1_url = 's3://'.$this->container->getParameter('aws_bucket').'/'.$file_name_with_ext;

                    $s3_https_url_demo2 = $s3_upload_demo2['ObjectURL'];
                    $s3_https_url_screenshot_demo2 = $s3_upload_screenshot_demo2['ObjectURL'];
                    $file_name_with_ext = basename( $s3_https_url_demo2 );
                    $file_name_elts = explode('.', $file_name_with_ext);
                    $file_name_without_ext = $file_name_elts[0];
                    $video_demo2_url = 's3://'.$this->container->getParameter('aws_bucket').'/'.$file_name_with_ext;

                    // Get the file size
                    $video_demo1_size = filesize($video1_file);
                    $video_demo2_size = filesize($video2_file);

                    // Update the DB with the new URL
                    $video1->setS3Url($s3_https_url_demo1);
                    $video1->setS3ScreenshotUrl($s3_https_url_screenshot_demo1);
                    $video1->setSize($video_demo1_size);
                    $video2->setS3Url($s3_https_url_demo2);
                    $video2->setS3ScreenshotUrl($s3_https_url_screenshot_demo2);
                    $video2->setSize($video_demo2_size);
                    $em->persist($video1);
                    $em->persist($video2);
                    $em->flush();

                    // Set the storage used for the user
                    $total_amount_space_used_by_user = $video_demo1_size + $video_demo2_size;
                    $user->setStorageUsed($total_amount_space_used_by_user);
                    $em->persist($user);
                    $em->flush();
                }

                // If an error occured during the upload to Amazon S3
                else {

                    // We delete the video and the encoding job in the database
                    $em->remove($video1);
                    $em->remove($video2);
                    $em->flush();

                    // Set to 0 the storage used by the user
                    $user->setStorageUsed( 0 );
                    $em->persist($user);
                    $em->flush();
                }


                // Prepare the response
                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["user_id"] = $user->getSecretId();

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
     * @Route("/user/{secret_id}/edit")
     * @Template()
     */
    public function editUserAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/edit"
    	);

    	// Get the data
        $stripe_id = $request->request->get('stripe_id', NULL);
        $stripe_sub_id = $request->request->get('stripe_sub_id', NULL);
    	$email = $request->request->get('email', NULL);
        $old_password = $request->request->get('old_password', NULL);
    	$new_password = $request->request->get('new_password', NULL);
    	$new_password_repeat = $request->request->get('new_password_repeat', NULL);
    	$first_name = $request->request->get('first_name', NULL);
    	$last_name = $request->request->get('last_name', NULL);
    	$company = $request->request->get('company', NULL);
    	$address = $request->request->get('address', NULL);
    	$address_more = $request->request->get('address_more', NULL);
    	$zip = $request->request->get('zip', NULL);
    	$state = $request->request->get('state', NULL);
    	$city = $request->request->get('city', NULL);
    	$country = $request->request->get('country', NULL);
        $vat_number = $request->request->get('vat_number', NULL);
    	$offer_id = $request->request->get('offer', NULL);
        $coupon_id = $request->request->get('coupon', NULL);
    	$storage_used = $request->request->get('storage_used', 0);
    	$status = $request->request->get('status', NULL);
        $role = $request->request->get('role', NULL);
        $avatar = $request->request->get('avatar', NULL);
    	$opt_in = $request->request->get('opt_in', NULL);
        $mail_notification = $request->request->get('mail_notification', NULL);
        $locale = $request->request->get('locale', NULL);
        $user_google_id = $request->request->get('user_google_id', NULL);
        $user_google_image = $request->request->get('user_google_image', NULL);
        $user_youtube_id = $request->request->get('user_youtube_id', NULL);
        $user_facebook_id = $request->request->get('user_facebook_id', NULL);
        $user_facebook_image = $request->request->get('user_facebook_image', NULL);
        $slack_webhook_url = $request->request->get('slack_webhook_url', NULL);

        $logger = $this->get("logger");
        $logger->info("Youtube: ".$user_youtube_id);

        $empty_company = $request->request->get('empty_company', 0);
        $empty_address = $request->request->get('empty_address', 0);
        $empty_address_more = $request->request->get('empty_address_more', 0);
        $empty_zip = $request->request->get('empty_zip', 0);
        $empty_state = $request->request->get('empty_state', 0);
        $empty_city = $request->request->get('empty_city', 0);
        $empty_country = $request->request->get('empty_country', 0);
        $empty_vat_number = $request->request->get('empty_vat_number', 0);
        $empty_avatar = $request->request->get('empty_avatar', 0);
        $empty_stripe_sub_id = $request->request->get('empty_stripe_sub_id', 0);

        // Change the format of the email
        $email = strtolower($email);

        // Get the user
        $em = $this->getDoctrine()->getManager();
        $user = new User;
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

    	// If a new email is provided, we check that the email is unique.
    	if(is_object($user) && ($user != NULL)) {
	    	if(($email != NULL) && (strcmp($email, $user->getEmail()) != 0)) {

	    		$check_email = new User;
	    		$check_email = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('email' => $email));
	    	}
	    	else {
	    		$check_email = NULL;
	    	}
	    }

    	// Else, we set the $check_email variable to NULL
    	else {
    		$check_email = NULL;
    	}

        // If the type of request used is not the one expected.
        if(!$request->isMethod('PUT')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["error_source"] = "not_put_request";
            $json["message"] = "This is not a PUT request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If this email is already used
        elseif($check_email != NULL) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_source"] = "email_already_used";
            $json["message"] = "This email is already used.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($user) || ($user == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_source"] = "user_doesnt_exist";
            $json["message"] = "This user doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the user
            try {

                // If we have to change the offer associated to the user,
                // Get the offer details
                if($offer_id != NULL) {
                	$offer_details = new Offer;
    				$offer_details = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('secret_id' => $offer_id));
                	$user->setOffer($offer_details);
                }

                // If the email changes, and the user opted_in
                // update Mailchimp accordingly
            	if(($email != NULL) && (strcmp($email, $user->getEmail()) != 0)) {

            		// Check if the user opted in
            		if(($opt_in == 1)
            			|| ($user->getOptIn() == 1)) {

            			// Create Mailchimp Manager
	                	$mailchimp_manager = new MailchimpManager();
		                $lists = json_decode( $mailchimp_manager->getLists() );

		                // We loop through the lists to make sure that the list of the clients exists.
		                foreach ($lists->{"lists"} as $list) {

		                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

		                		// Set the mailchimp parameters
		                		$mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');

                                // Get the member
                                $mailchimp_manager->email = $user->getEmail();
                                $mailchimp_member = $mailchimp_manager->getMember();
                                $mailchimp_member = json_decode($mailchimp_member);

                                // Set the shared parameters of the requests
                                if($first_name != NULL)
				                	$mailchimp_manager->first_name = $first_name;
				                else
				                	$mailchimp_manager->first_name = $user->getFirstName();

				                if($last_name != NULL)
				                	$mailchimp_manager->last_name = $last_name;
				                else
				                	$mailchimp_manager->last_name = $user->getLastName();

                                if($locale != NULL) {
                                    $mailchimp_manager->locale = strtoupper( $locale );
                                }
                                else {
                                    $mailchimp_manager->locale = strtoupper( $user->getLocale() );
                                }

                                // If the user doesn't exist in Mailchimp
                                if($mailchimp_member->{'status'} == 404) {
                                    $mailchimp_manager->email = $email;
                                    $subscription = $mailchimp_manager->subscribeMember();
                                }
                                else {
                                    $mailchimp_manager->old_email = $user->getEmail();
                                    $mailchimp_manager->email = $email;
    			                	$subscription = $mailchimp_manager->editMember();
                                }

                                // Return the result of the subscription
		                		$json["mailchimp_subscription"] = json_decode( $subscription );
		                	}
		                }
            		}

            		// Set the new email address
            		$user->setEmail($email);

                    // Check if the user already needs to confirm his email address.
                    $email_to_confirm = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user));

                    // Add this user to the list of emails to confirm
                    if($email_to_confirm == NULL) {
                        $email_to_confirm = new EmailToConfirm;
                        $email_to_confirm->setUser($user);
                        $email_to_confirm->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
                        $em->persist($email_to_confirm);
                    }
                    else {
                        $email_to_confirm->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
                        $em->persist($email_to_confirm);
                    }
            	}

                // If the opt_in changes, change Mailchimp accordingly
                if(($opt_in != NULL) && ($opt_in != $user->getOptIn())) {

                	// Create Mailchimp Manager
                	$mailchimp_manager = new MailchimpManager();
	                $lists = json_decode( $mailchimp_manager->getLists() );

	                // We loop through the lists to make sure that the list of the clients exists.
	                foreach ($lists->{"lists"} as $list) {

	                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

	                		// Set the mailchimp parameters
	                		$mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');

                            // Get the member
                            $mailchimp_manager->email = $user->getEmail();
                            $mailchimp_member = $mailchimp_manager->getMember();
                            $mailchimp_member = json_decode($mailchimp_member);

                            if($locale != NULL) {
                                $mailchimp_manager->locale = strtoupper( $locale );
                            }
                            else {
                                $mailchimp_manager->locale = strtoupper( $user->getLocale() );
                            }

                            // If there is a request to unsubscribe the user, and the user has been found in Mailchimp
	                		if(($opt_in == 0) && ($mailchimp_member->{'status'} != 404)) {

	                			// Trigger the unsubscription of the old email address
		                		$subscription = $mailchimp_manager->unsubscribeMember();
		                	}
		                	elseif($opt_in == 1) {

                                // If the user already exists in Mailchimp, edit his profile
                                if($mailchimp_member->{'status'} != 404) {

    		                		// Trigger the subscription of the new email address
                                    $mailchimp_manager->old_email = $user->getEmail();
    		                		$mailchimp_manager->status = "subscribed";
    		                		$subscription = $mailchimp_manager->editMember();
                                }

                                // If the user doesn't exist yet in Mailchimp, create the subscription
                                else {
                                    $subscription = $mailchimp_manager->subscribeMember();
                                }
		                	}

	                		// Return the result of the subscription
	                		$json["mailchimp_subscription"] = json_decode( $subscription );
	                	}
	                }

	                // Set the new opt_in value
                	$user->setOptIn($opt_in);
                }

                // If the firstname of the user changes, change Mailchimp accordingly
                if((strcmp($first_name, $user->getFirstName()) != 0) || (strcmp($last_name, $user->getLastName()) != 0)) {

                	// Create Mailchimp Manager
                	$mailchimp_manager = new MailchimpManager();
	                $lists = json_decode( $mailchimp_manager->getLists() );

	                // We loop through the lists to make sure that the list of the clients exists.
                    if($lists != NULL) {
    	                foreach ($lists->{"lists"} as $list) {

    	                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

    	                		// Set the mailchimp parameters
                                $mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');

                                // Get the member
                                $mailchimp_manager->email = $user->getEmail();
                                $mailchimp_member = $mailchimp_manager->getMember();
                                $mailchimp_member = json_decode( $mailchimp_member );

                                // If the user exists in Mailchimp, edit his profile
                                if($mailchimp_member->{'status'} != 404) {
                                    $mailchimp_manager->old_email = $user->getEmail();

                                    if(strcmp($first_name, $user->getFirstName()) != 0)
                                        $mailchimp_manager->first_name = $first_name;
                                    if(strcmp($last_name, $user->getLastName()) != 0)
                                        $mailchimp_manager->last_name = $last_name;

                                    $subscription = $mailchimp_manager->editMember();

        	                		// Return the result of the subscription
        	                		$json["mailchimp_subscription"] = json_decode( $subscription );
                                }

    	                		// Return the result of the subscription
    	                		$json["mailchimp_subscription"] = NULL;
    	                	}
    	                }
                    }
                }

                // If the locale of the user changes, change Mailchimp accordingly
                if((strcmp($locale, $user->getLocale()) != 0) && ($locale != NULL)) {

                	// Create Mailchimp Manager
                	$mailchimp_manager = new MailchimpManager();
	                $lists = json_decode( $mailchimp_manager->getLists() );

	                // We loop through the lists to make sure that the list of the clients exists.
                    if($lists != NULL) {
    	                foreach ($lists->{"lists"} as $list) {

    	                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

    	                		// Set the mailchimp parameters
                                $mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');

                                // Get the member
                                $mailchimp_manager->email = $user->getEmail();
                                $mailchimp_member = $mailchimp_manager->getMember();
                                $mailchimp_member = json_decode( $mailchimp_member );

                                // If the user exists in Mailchimp, edit his profile
                                if($mailchimp_member->{'status'} != 404) {
                                    $mailchimp_manager->old_email = $user->getEmail();
                                    $mailchimp_manager->locale = strtoupper($locale);

                                    $subscription = $mailchimp_manager->editMember();

        	                		// Return the result of the subscription
        	                		$json["mailchimp_subscription"] = json_decode( $subscription );
                                }

    	                		// Return the result of the subscription
    	                		$json["mailchimp_subscription"] = NULL;
    	                	}
    	                }
                    }
                }

                // If the status of the user changes, change Mailchimp accordingly
                if((strcmp($status, $user->getStatus()) != 0) && ($status != NULL)) {

                	// Create Mailchimp Manager
                	$mailchimp_manager = new MailchimpManager();
	                $lists = json_decode( $mailchimp_manager->getLists() );

	                // We loop through the lists to make sure that the list of the clients exists.
                    if($lists != NULL) {
    	                foreach ($lists->{"lists"} as $list) {

    	                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

    	                		// Set the mailchimp parameters
                                $mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');

                                // Get the member
                                $mailchimp_manager->email = $user->getEmail();
                                $mailchimp_member = $mailchimp_manager->getMember();
                                $mailchimp_member = json_decode( $mailchimp_member );

                                // If the user exists in Mailchimp, edit his profile
                                if($mailchimp_member->{'status'} != 404) {
                                    $mailchimp_manager->old_email = $user->getEmail();
                                    $mailchimp_manager->active = $status;

                                    $subscription = $mailchimp_manager->editMember();

        	                		// Return the result of the subscription
        	                		$json["mailchimp_subscription"] = json_decode( $subscription );
                                }

    	                		// Return the result of the subscription
    	                		$json["mailchimp_subscription"] = NULL;
    	                	}
    	                }
                    }
                }

                // If the password changes
                if($new_password != NULL) {

                    // We check the old password
                    $old_password_time = $user->getPasswordTime();
                    $old_password_hash = hash('sha512', $old_password_time.$old_password.$this->container->getParameter('secret'));

                    // If the old password is correct or if $old_password is defined as "strime-reset-password" (this is a reset case)
                    if((strcmp($user->getPassword(), $old_password_hash) == 0) || (strcmp($old_password, "strime-reset-password") == 0) ) {
                        // We encrypt the password
                        $new_password_time = time();
                        $new_password = hash('sha512', $new_password_time.$new_password.$this->container->getParameter('secret'));

                        $user->setPassword($new_password);
                        $user->setPasswordTime($new_password_time);
                    }

                    // If the old password is incorrect
                    else {
                        // Set the content of the response
                        $json["status"] = "error";
                        $json["response_code"] = "400";
                        $json["error_source"] = "password_incorrect";
                        $json["message"] = "The password you provided is incorrect.";

                        // Create the response object and initialize it
                        $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                        return $response;
                        die;
                    }
                }

                // Get the address details
                $address_details = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user));

                // Set the new values
                if($stripe_id != NULL)
                    $user->setStripeId($stripe_id);
                if(($stripe_sub_id != NULL) && ($empty_stripe_sub_id != 1))
                    $user->setStripeSubId($stripe_sub_id);
                if($first_name != NULL)
                	$user->setFirstName($first_name);
                if($last_name != NULL)
                	$user->setLastName($last_name);
                if($company != NULL)
                	$user->setCompany($company);
                if($vat_number != NULL)
                    $user->setVatNumber($vat_number);
                if($storage_used != NULL)
                	$user->setStorageUsed($storage_used);
                if($status != NULL)
                	$user->setStatus($status);
                if($role != NULL)
                    $user->setRole($role);
                if($avatar != NULL)
                    $user->setAvatar($avatar);
                if($locale != NULL)
                    $user->setLocale($locale);
                if($mail_notification != NULL)
                    $user->setMailNotification($mail_notification);

                // Edit the coupons if needed
                $current_coupons = $user->getCoupons();

                if($coupon_id != NULL) {
                    $coupon = $em->getRepository('StrimeAPIUserBundle:Coupon')->findOneBy(array('stripe_id' => $coupon_id));

                    if($coupon != NULL) {
                        $coupon_already_exists = FALSE;

                        foreach ($current_coupons as $current_coupon) {
                            if($current_coupon->getId() == $coupon->getId()) {
                                $coupon_already_exists = TRUE;
                            }
                        }

                        if(!$coupon_already_exists) {
                            $user->addCoupon($coupon);
                        }
                    }
                }

                if($empty_company == 1)
                    $user->setCompany(NULL);
                if($empty_vat_number == 1)
                    $user->setVatNumber(NULL);
                if($empty_avatar == 1)
                    $user->setAvatar(NULL);
                if($empty_stripe_sub_id == 1)
                    $user->setStripeSubId(NULL);

                $em->persist($user);
                $em->flush();


                // If a Google ID has been passed as a parameter
                if(($user_google_id != NULL) || ($user_google_image != NULL)) {

                    // Check if there is already a record for this user
                    $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user));

                    // If there is already a record for this user, update the Google ID
                    if($user_google != NULL) {
                        if($user_google_id != NULL)
                            $user_google->setGoogleId($user_google_id);
                        if($user_google_image != NULL)
                            $user_google->setGoogleImage($user_google_image);
                    }
                    else {
                        $user_google = new UserGoogle;
                        $user_google->setUser($user);

                        if($user_google_id != NULL)
                            $user_google->setGoogleId($user_google_id);
                        if($user_google_image != NULL)
                            $user_google->setGoogleImage($user_google_image);
                    }

                    $em->persist($user_google);
                    $em->flush();
                }


                // If a Youtube ID has been passed as a parameter
                if($user_youtube_id != NULL) {

                    // Check if there is already a record for this user
                    $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

                    // If there is already a record for this user, update the Google ID
                    if($user_youtube != NULL) {
                        if($user_youtube_id != NULL)
                            $user_youtube->setYoutubeId($user_youtube_id);
                    }
                    else {
                        $user_youtube = new UserYoutube;
                        $user_youtube->setUser($user);

                        if($user_youtube_id != NULL)
                            $user_youtube->setYoutubeId($user_youtube_id);
                    }

                    $em->persist($user_youtube);
                    $em->flush();
                }


                // If a Facebook ID has been passed as a parameter
                if(($user_facebook_id != NULL) || ($user_facebook_image != NULL)) {

                    // Check if there is already a record for this user
                    $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user));

                    // If there is already a record for this user, update the Google ID
                    if($user_facebook != NULL) {
                        if($user_facebook_id != NULL)
                            $user_facebook->setFacebookId($user_facebook_id);
                        if($user_facebook_image != NULL)
                            $user_facebook->setFacebookImage($user_facebook_image);
                    }
                    else {
                        $user_facebook = new UserFacebook;
                        $user_facebook->setUser($user);

                        if($user_facebook_id != NULL)
                            $user_facebook->setFacebookId($user_facebook_id);
                        if($user_facebook_image != NULL)
                            $user_facebook->setFacebookImage($user_facebook_image);
                    }

                    $em->persist($user_facebook);
                    $em->flush();
                }


                // If a Slack Webhook URL has been passes as a parameter
                if($slack_webhook_url != NULL) {

                    // Check if there is already a record for this user
                    $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user));

                    // If there is already a record for this user, update the Webhook URL
                    if($user_slack != NULL) {
                        $user_slack->setWebhookUrl($slack_webhook_url);
                    }
                    else {
                        $user_slack = new UserSlack;
                        $user_slack->setUser($user);
                        $user_slack->setWebhookUrl($slack_webhook_url);
                    }

                    $em->persist($user_slack);
                    $em->flush();
                }
                else {

                    // Check if there is already a record for this user
                    $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user));

                    // If there is already a record for this user, remove it
                    if($user_slack != NULL) {
                        $em->remove($user_slack);
                        $em->flush();
                    }
                }


                // Set the new values of the address
                if($address_details != NULL) {
                    if($address != NULL)
                        $address_details->setAddress($address);
                    if($address_more != NULL)
                        $address_details->setAddressMore($address_more);
                    if($zip != NULL)
                        $address_details->setZip($zip);
                    if($city != NULL)
                        $address_details->setCity($city);
                    if($state != NULL)
                        $address_details->setState($state);
                    if($country != NULL)
                        $address_details->setCountry($country);

                    if($empty_address == 1)
                        $address_details->setAddress(NULL);
                    if($empty_address_more == 1)
                        $address_details->setAddressMore(NULL);
                    if($empty_zip == 1)
                        $address_details->setZip(NULL);
                    if($empty_city == 1)
                        $address_details->setCity(NULL);
                    if($empty_state == 1)
                        $address_details->setState(NULL);
                    if($empty_country == 1)
                        $address_details->setCountry(NULL);

                    $em->persist($address_details);
                    $em->flush();


                    // Create the complete address
                    $complete_address = $this->getCompleteAddress($address_details->getAddress(), $address_details->getAddressMore(), $address_details->getZip(), $address_details->getCity(), $address_details->getState(), $address_details->getCountry());

                    // Get the coordinates
                    if(strlen($complete_address) > 0) {

                        // Geocode their address
                        $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
                        $geocoder = new \Geocoder\Provider\GoogleMaps($curl);

                        try {
                            $results = $geocoder->geocode( $complete_address );

                            // Get the latitude and longitude
                            $result = $results->first();
                            $latitude = $result->getLatitude();
                            $longitude = $result->getLongitude();
                        }
                        catch (\Exception $e) {
                            $latitude = NULL;
                            $longitude = NULL;
                        }
                    }

                    // Else, set NULL values
                    else {
                        $latitude = NULL;
                        $longitude = NULL;
                    }

                    // Update the address object the coordinates
                    $address_details->setLatitude($latitude);
                    $address_details->setLongitude($longitude);
                    $em->persist($address_details);
                    $em->flush();
                }

                // Else, if no address exists yet
                else {

                    $address_details = new Address;


                    // Create the complete address
                    $complete_address = $this->getCompleteAddress($address, $address_more, $zip, $city, $state, $country);

                    // Get the coordinates
                    if(strlen($complete_address) > 0) {

                        // Geocode their address
                        $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
                        $geocoder = new \Geocoder\Provider\GoogleMaps($curl);

                        try {
                            $results = $geocoder->geocode( $complete_address );

                            // Get the latitude and longitude
                            $result = $results->first();
                            $latitude = $result->getLatitude();
                            $longitude = $result->getLongitude();
                        }
                        catch (\Exception $e) {
                            $latitude = NULL;
                            $longitude = NULL;
                        }
                    }

                    // Else, set NULL values
                    else {
                        $latitude = NULL;
                        $longitude = NULL;
                    }

                    $address_details->setUser($user);
                    $address_details->setAddress($address);
                    $address_details->setAddressMore($address_more);
                    $address_details->setZip($zip);
                    $address_details->setCity($city);
                    $address_details->setState($state);
                    $address_details->setCountry($country);
                    $address_details->setLatitude($latitude);
                    $address_details->setLongitude($longitude);
                    $em->persist($address_details);
                    $em->flush();
                }

                $json["status"] = "success";
                $json["response_code"] = "200";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_saving_data";
                $json["message"] = "An error occured while editing data in the database.";

	        	// Create the response object and initialize it
	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/delete")
     * @Template()
     */
    public function deleteUserAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/delete"
    	);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $user = new User;

    	// Get the user
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($user) || ($user == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This user ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We delete the user and its content
            try {

                // Get his videos
                $videos = $em->getRepository('StrimeAPIVideoBundle:Video')->findBy(array('user' => $user));

                // Delete his videos
                if( is_array($videos) ) {

                    $flag_videos_deletion = TRUE;

                    foreach ($videos as $video) {

                        // Set the object
                        $video_action = $this->container->get('strime_api.helpers.video_action');
                        $video_action->aws_key = $this->container->getParameter('aws_key');
                        $video_action->aws_secret = $this->container->getParameter('aws_secret');
                        $video_action->aws_region = $this->container->getParameter('aws_region');
                        $video_action->aws_bucket = $this->container->getParameter('aws_bucket');
                        $video_action->video = $video;

                        // Delete the files on Amazon
                        $video_action->deleteVideoFromAmazon();

                        // We first delete the comments who have a parent
                        $video_action->deleteVideoCommentsWithParent();

                        // We then delete all the other comments
                        $video_action->deleteVideoCommentsWithoutParent();

                        // We update the space available for this user
                        $video_action->updateUserSpaceAvailable();

                        // Clean the EncodingJobTime table
                        $video_action->deleteAssociatedEncodingJobsTimes();

                        // We delete the associated encoding jobs
                        $video_action->deleteAssociatedEncodingJobs();

                        // We delete the associated Youtube items
                        $video_action->deleteYoutubeIds();

                        // We delete all the associated contacts
                        $video_action->deleteAssociatedContacts();

                        // We delete the video
                        try {
                            $em->remove($video);
                            $em->flush();
                        }
                        catch (Exception $e) {
                            $flag_videos_deletion = FALSE;
                        }
                    }
                }

                // We get all the images associated to this user
                $images = $em->getRepository('StrimeAPIImageBundle:Image')->findBy(array('user' => $user));

                // Delete all the images included if the user
                if(is_array($images)) {

                    $flag_images_deletion = TRUE;

                    foreach ($images as $image) {

                        // Set the object
                        $image_action = $this->container->get('strime_api.helpers.image_action');
                        $image_action->aws_key = $this->container->getParameter('aws_key');
                        $image_action->aws_secret = $this->container->getParameter('aws_secret');
                        $image_action->aws_region = $this->container->getParameter('aws_region');
                        $image_action->aws_bucket = $this->container->getParameter('aws_bucket');
                        $image_action->image = $image;

                        // Delete the files on Amazon
                        $image_action->deleteImageFromAmazon();

                        // We first delete the comments who have a parent
                        $image_action->deleteImageCommentsWithParent();

                        // We then delete all the other comments
                        $image_action->deleteImageCommentsWithoutParent();

                        // We update the space available for this user
                        $image_action->updateUserSpaceAvailable();

                        // We delete all the associated contacts
                        $image_action->deleteAssociatedContacts();

                        // We delete the image
                        try {
                            $em->remove($image);
                            $em->flush();
                        }
                        catch (Exception $e) {
                            $flag_images_deletion = FALSE;
                        }
                    }
                }

                // Get his audio files
                $audios = $em->getRepository('StrimeAPIAudioBundle:Audio')->findBy(array('user' => $user));

                // Delete his audios
                if( is_array($audios) ) {

                    $flag_audios_deletion = TRUE;

                    foreach ($audios as $audio) {

                        // Set the object
                        $audio_action = $this->container->get('strime_api.helpers.audio_action');
                        $audio_action->aws_key = $this->container->getParameter('aws_key');
                        $audio_action->aws_secret = $this->container->getParameter('aws_secret');
                        $audio_action->aws_region = $this->container->getParameter('aws_region');
                        $audio_action->aws_bucket = $this->container->getParameter('aws_bucket');
                        $audio_action->video = $audio;

                        // Delete the files on Amazon
                        $audio_action->deleteAudioFromAmazon();

                        // We first delete the comments who have a parent
                        $audio_action->deleteAudioCommentsWithParent();

                        // We then delete all the other comments
                        $audio_action->deleteAudioCommentsWithoutParent();

                        // We update the space available for this user
                        $audio_action->updateUserSpaceAvailable();

                        // Clean the EncodingJobTime table
                        $audio_action->deleteAssociatedEncodingJobsTimes();

                        // We delete the associated encoding jobs
                        $audio_action->deleteAssociatedEncodingJobs();

                        // We delete all the associated contacts
                        $audio_action->deleteAssociatedContacts();

                        // We delete the audio
                        try {
                            $em->remove($audio);
                            $em->flush();
                        }
                        catch (Exception $e) {
                            $flag_audios_deletion = FALSE;
                        }
                    }
                }



                // Make sure there is no remaining comments
                // Set the object
                $user_action = $this->container->get('strime_api.helpers.user_helper');
                $user_action->user = $user;

                // Anonymize all the comments of the user
                $user_action->anonymizeComments();



                // Get his projects
                $projects = $em->getRepository('StrimeAPIVideoBundle:Project')->findByUser($user);

                // Delete his projects
                if( is_array($projects) ) {
                    foreach ($projects as $project) {

                        // We get all the encoding jobs associated to the project
                        $encoding_jobs = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findBy(array('project' => $project));

                        // Foreach encoding job, delete it
                        if(is_array($encoding_jobs)) {
                            foreach ($encoding_jobs as $encoding_job) {
                                $em->remove($encoding_job);
                                $em->flush();
                            }
                        }

                        $em->remove($project);
                        $em->flush();
                    }
                }

                // Get his (potentially) remaining encoding jobs
                $encoding_jobs = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findByUser($user);

                // Delete his projects
                if( is_array($encoding_jobs) ) {
                    foreach ($encoding_jobs as $encoding_job) {

                        $em->remove($encoding_job);
                        $em->flush();
                    }
                }

                // Get his contacts
                $contacts = $em->getRepository('StrimeAPIUserBundle:Contact')->findBy(array('user' => $user));

                // Delete his contacts
                if( is_array($contacts) ) {
                    foreach ($contacts as $contact) {

                        $em->remove($contact);
                        $em->flush();
                    }
                }

                // Get his tokens
                $tokens = $em->getRepository('StrimeAPIUserBundle:Token')->findByUser($user);

                // Delete his tokens
                if( is_array($tokens) ) {
                    foreach ($tokens as $token) {

                        $em->remove($token);
                        $em->flush();
                    }
                }

                // Check if there is an entry in the UserGoogle table
                $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findByUser($user);

                if( is_array($user_google) ) {
                    foreach ($user_google as $google_item) {

                        $em->remove($google_item);
                        $em->flush();
                    }
                }

                // Check if there is an entry in the UserYoutube table
                $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findByUser($user);

                if( is_array($user_youtube) ) {
                    foreach ($user_youtube as $youtube_item) {

                        $em->remove($youtube_item);
                        $em->flush();
                    }
                }

                // Check if there is an entry in the UserFacebook table
                $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findByUser($user);

                if( is_array($user_facebook) ) {
                    foreach ($user_facebook as $facebook_item) {

                        $em->remove($facebook_item);
                        $em->flush();
                    }
                }

                // Check if there is an entry in the UserSlack table
                $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findByUser($user);

                if( is_array($user_slack) ) {
                    foreach ($user_slack as $slack_item) {

                        $em->remove($slack_item);
                        $em->flush();
                    }
                }

                // Get his address
                $addresses = $em->getRepository('StrimeAPIUserBundle:Address')->findByUser($user);

                // Delete his projects
                if( is_array($addresses) ) {
                    foreach ($addresses as $address) {

                        $em->remove($address);
                        $em->flush();
                    }
                }

                // Delete all the associated coupons
                $coupons = $user->getCoupons();

                if(($coupons != NULL) && (is_array($coupons))) {
                    foreach ($coupons as $coupon) {
                        $user->removeCoupon($coupon);
                        $em->flush();
                    }
                }

                // If he still needed to confirm his email address, delete this entry
                $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findByUser($user);

                if($needs_to_confirm_email != NULL) {
                    foreach ($needs_to_confirm_email as $entry) {

                        $em->remove($entry);
                        $em->flush();
                    }
                }

            	// Keep the user email in a variable.
            	$email = $user->getEmail();

                // Unsubscribe the user from Mailchimp
                // Create Mailchimp Manager
            	$mailchimp_manager = new MailchimpManager();
                $lists = json_decode( $mailchimp_manager->getLists() );

                // We loop through the lists to make sure that the list of the clients exists.
                foreach ($lists->{"lists"} as $list) {

                	if(strcmp($list->{"id"}, $this->container->getParameter('mailchimp_clients_list')) == 0) {

                		// Set the mailchimp parameters
                		$mailchimp_manager->email = $email;
                		$mailchimp_manager->list = $this->container->getParameter('mailchimp_clients_list');
                		$subscription = $mailchimp_manager->unsubscribeMember();

                		// Return the result of the subscription
                		$json["mailchimp_subscription"] = json_decode( $subscription );
                	}
                }

                // Get his invoices
                $invoices = $em->getRepository('StrimeAPIUserBundle:Invoice')->findBy(array('user' => $user));

                // Change the contact for his invoices and set a complete URL to download it
                if( is_array($invoices) ) {
                    foreach ($invoices as $invoice) {

                        $invoice->setDeletedUserId( $user->getSecretId() );
                        $invoice->setUserName( $user->getFirstName() . " " . $user->getLastName() );
                        $invoice->setUser( NULL );
                        $em->persist( $invoice );
                        $em->flush();
                    }
                }

                // Delete the user
                $em->remove( $user );
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


    /**
     * @Route("/user/signin")
     * @Template()
     */
    public function signinUserAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/user/signin"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();

        // Get the data
        $email = $request->request->get('email', NULL);
        $password = $request->request->get('password', NULL);

        // Change the format of the email
        $email = strtolower($email);

        // We check that the email is unique.
        $user = new User;
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('email' => $email));

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
        elseif(($email == NULL) || ($password == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "Some data are missing.";
            $json["error_source"] = "missing_data";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If this email is already used
        elseif($user == NULL) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "Email or password invalid.";
            $json["error_source"] = "email_or_password_invalid";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If this email is already used
        elseif(strcmp($user->getStatus(), "deactived") == 0) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "Account deactived.";
            $json["error_source"] = "account_deactivated";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We encrypt the password provided
            $password_time = $user->getPasswordTime();
            $hashed_password = hash('sha512', $password_time.$password.$this->container->getParameter('secret'));
            $saved_password = $user->getPassword();

            if(strcmp($hashed_password, $saved_password) == 0) {

                // Get the details of the offer subscribed
                $offer = $em->getRepository('StrimeAPIUserBundle:Offer')->findOneBy(array('id' => $user->getOffer()));

                // Get the details of the address
                $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user));

                // Prepare the offer content
                if($offer != NULL) {
                    $offer_content = array(
                        "offer_id" => $offer->getSecretId(),
                        "name" => $offer->getName(),
                        "price" => $offer->getPrice(),
                        "storage_allowed" => $offer->getStorageAllowed()
                    );
                }
                else {
                    $offer_content = array();
                }

                // Prepare the list of rights
                $user_helper = $this->container->get('strime_api.helpers.user_helper');
                $user_helper->user = $user;
                $rights_list = $user_helper->setUserRights();

                // Update the value of last_login in the user profile
                $user->setLastLogin( time() );
                $em->persist($user);
                $em->flush();

                // Check if the user has a connection with Google
                $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user));

                if($user_google != NULL) {
                    $user_google_details = array(
                        "google_id" => $user_google->getGoogleId(),
                        "google_image" => $user_google->getGoogleImage()
                    );
                }
                else {
                    $user_google_details = NULL;
                }

                // Check if the user has a connection with Youtube
                $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

                if($user_youtube != NULL) {
                    $user_youtube_details = array(
                        "youtube_id" => $user_youtube->getYoutubeId()
                    );
                }
                else {
                    $user_youtube_details = NULL;
                }

                // Check if the user has a connection with Facebook
                $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user));

                if($user_facebook != NULL) {
                    $user_facebook_details = array(
                        "facebook_id" => $user_facebook->getFacebookId(),
                        "facebook_image" => $user_facebook->getFacebookImage()
                    );
                }
                else {
                    $user_facebook_details = NULL;
                }

                // Check if the user has a connection with Slack
                $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_slack != NULL) {
                    $user_slack_details = array(
                        "webhook_url" => $user_slack->getWebhookUrl()
                    );
                }
                else {
                    $user_slack_details = NULL;
                }

                // Set the avatar
                $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
                $user_avatar = $avatar_helper->setUserAvatar($user, $user_google_details, $user_facebook_details);

                // Prepare the response
                $json["status"] = "success";
                $json["response_code"] = "200";
                $json["user"] = array(
                    "user_id" => $user->getSecretId(),
                    "first_name" => $user->getFirstName(),
                    "last_name" => $user->getLastName(),
                    "email" => $user->getEmail(),
                    "offer" => $offer_content,
                    "rights" => $rights_list,
                    "storage_used" => $user->getStorageUsed(),
                    "status" => $user->getStatus(),
                    "role" => $user->getRole(),
                    "avatar" => $user_avatar,
                    "locale" => $user->getLocale(),
                    "mail_notification" => $user->getMailNotification(),
                    "user_google_details" => $user_google_details,
                    "user_youtube_details" => $user_youtube_details,
                    "user_facebook_details" => $user_facebook_details,
                    "user_slack_details" => $user_slack_details
                );

                if($address != NULL)
                    $json["user"]["country"] = $address->getCountry();
                else
                    $json["user"]["country"] = NULL;

                // Create the response object and initialize it
                $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            else {
                // Prepare the response
                $json["status"] = "error";
                $json["response_code"] = "400";
                $json["message"] = "Email or password invalid";
                $json["error_source"] = "email_or_password_invalid";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/search")
     * @Template()
     */
    public function searchUserAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/user/search"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();

        // Get the data
        $search = $request->request->get('search', NULL);

        // Search users
        $repo = $em->getRepository('StrimeAPIUserBundle:User');
        $query = $repo->createQueryBuilder('u')
                       ->where('u.email LIKE :email OR u.first_name LIKE :firstname OR u.last_name LIKE :lastname')
                       ->orderBy('u.last_name', 'ASC')
                       ->setParameter('email', '%'.$search.'%')
                       ->setParameter('firstname', '%'.$search.'%')
                       ->setParameter('lastname', '%'.$search.'%')
                       ->getQuery();

        $users = $query->getResult();

        // If we get a result
        if($users != NULL) {

            // Set the results
            $users_results = array();

            // For each user, set its details
            foreach ($users as $user) {

                // Prepare the offer content
                if($user->getOffer() != NULL) {
                    $offer_content = array(
                        "offer_id" => $user->getOffer()->getSecretId(),
                        "name" => $user->getOffer()->getName(),
                        "price" => $user->getOffer()->getPrice()
                    );
                }
                else {
                    $offer_content = array();
                }

                // Prepare the list of rights
                $user_helper = $this->container->get('strime_api.helpers.user_helper');
                $user_helper->user = $user;
                $rights_list = $user_helper->setUserRights();

                // Get the address details
                $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user));

                // Check if he needs to confirm his email address
                $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user));

                if($needs_to_confirm_email != NULL)
                    $needs_to_confirm_email = TRUE;
                else
                    $needs_to_confirm_email = FALSE;

                // Check if the user has a connection with Google
                $user_google = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_google != NULL) {
                    $user_google_details = array(
                        "google_id" => $user_google->getGoogleId(),
                        "google_image" => $user_google->getGoogleImage()
                    );
                }
                else {
                    $user_google_details = NULL;
                }

                // Check if the user has a connection with Youtube
                $user_youtube = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array('user' => $user));

                if($user_youtube != NULL) {
                    $user_youtube_details = array(
                        "youtube_id" => $user_youtube->getYoutubeId()
                    );
                }
                else {
                    $user_youtube_details = NULL;
                }

                // Check if the user has a connection with Facebook
                $user_facebook = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_facebook != NULL) {
                    $user_facebook_details = array(
                        "facebook_id" => $user_facebook->getFacebookId(),
                        "facebook_image" => $user_facebook->getFacebookImage()
                    );
                }
                else {
                    $user_facebook_details = NULL;
                }

                // Check if the user has a connection with Slack
                $user_slack = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array('user' => $user));

                // Set the variable with the result
                if($user_slack != NULL) {
                    $user_slack_details = array(
                        "webhook_url" => $user_slack->getWebhookUrl()
                    );
                }
                else {
                    $user_slack_details = NULL;
                }

                // Set the avatar
                $avatar_helper = $this->container->get('strime_api.helpers.avatar_helper');
                $user_avatar = $avatar_helper->setUserAvatar($user, $user_google_details, $user_facebook_details);

                // Prepare the array containing the results
                $user_data = array(
                    "user_id" => $user->getSecretId(),
                    "stripe_id" => $user->getStripeId(),
                    "stripe_sub_id" => $user->getStripeSubId(),
                    "email" => $user->getEmail(),
                    "first_name" => $user->getFirstName(),
                    "last_name" => $user->getLastName(),
                    "company" => $user->getCompany(),
                    "address" => $user->getAddress(),
                    "address_more" => $user->getAddressMore(),
                    "zip" => $user->getZip(),
                    "city" => $user->getCity(),
                    "state" => $user->getState(),
                    "country" => $user->getCountry(),
                    "vat_number" => $user->getVatNumber(),
                    "offer" => $offer_content,
                    "rights" => $rights_list,
                    "storage_used" => $user->getStorageUsed(),
                    "status" => $user->getStatus(),
                    "role" => $user->getRole(),
                    "avatar" => $user_avatar,
                    "opt_in" => $user->getOptIn(),
                    "mail_notification" => $user->getMailNotification(),
                    "needs_to_confirm_email" => $needs_to_confirm_email,
                    "user_google_details" => $user_google_details,
                    "user_youtube_details" => $user_youtube_details,
                    "user_facebook_details" => $user_facebook_details,
                    "user_slack_details" => $user_slack_details,
                    "last_login" => $user->getLastLogin(),
                    "locale" => $user->getLocale(),
                    "created_at" => $user->getCreatedAt(),
                    "updated_at" => $user->getUpdatedAt()
                );

                if($address != NULL) {
                    $user_data['address'] = $address->getAddress();
                    $user_data['address_more'] = $address->getAddressMore();
                    $user_data['zip'] = $address->getZip();
                    $user_data['city'] = $address->getCity();
                    $user_data['state'] = $address->getState();
                    $user_data['country'] = $address->getCountry();
                }
                else {
                    $user_data['address'] = NULL;
                    $user_data['address_more'] = NULL;
                    $user_data['zip'] = NULL;
                    $user_data['city'] = NULL;
                    $user_data['state'] = NULL;
                    $user_data['country'] = NULL;
                }

                $users_results[] = $user_data;
            }

            // Add the results to the response
            $json["results"] = $users_results;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $user = "No user has been found with this search pattern.";
            $json["results"] = NULL;
            $json["error_source"] = "no_result";
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{user_id}/confirm-email")
     * @Template()
     */
    public function confirmUserEmailAction(Request $request, $user_id)
    {

        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/user/{user_id}/confirm-email"
        );


        // Set Doctrine Manager and get the user object
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // If we get a result
        if($user != NULL) {

            // Check if he needs to confirm his email address
            $needs_to_confirm_email = $em->getRepository('StrimeAPIUserBundle:EmailToConfirm')->findOneBy(array('user' => $user));

            if($needs_to_confirm_email != NULL) {

                $em->remove( $needs_to_confirm_email );
                $em->flush();

                $json["message"] = "The email address of this user has been confirmed.";
                $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // If he is not part of the list of email addresses to confirm
            else {
                // Add the results to the response
                $json["message"] = "This user's email address didn't need to be confirmed.";
                $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // If no user has been created yet.
        else {
            $json["message"] = "No user has been created yet.";
            $json["results"] = array();
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/revoke-google")
     * @Template()
     */
    public function userRevokeGoogleAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/revoke-google"
    	);

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

    	// Get the user
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('GET')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a GET request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($user) || ($user == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This user ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Get his Google details
            $google_details = $em->getRepository('StrimeAPIUserBundle:UserGoogle')->findOneBy(array("user" => $user));

            // If we got a result, we delete it
            if($google_details != NULL) {

                try {
                    $em->remove($google_details);
                    $em->flush();

                    $json["status"] = "success";
                    $json["response_code"] = "200";

                    // Create the response object and initialize it
    	        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }

                catch (Exception $e) {
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["message"] = "An error occured while deleting data from the database.";

    	        	// Create the response object and initialize it
    	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/revoke-youtube")
     * @Template()
     */
    public function userRevokeYoutubeAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/revoke-youtube"
    	);

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

    	// Get the user
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('GET')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a GET request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($user) || ($user == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This user ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Get his Google details
            $youtube_details = $em->getRepository('StrimeAPIUserBundle:UserYoutube')->findOneBy(array("user" => $user));

            // If we got a result, we delete it
            if($youtube_details != NULL) {

                try {
                    $em->remove($youtube_details);
                    $em->flush();

                    $json["status"] = "success";
                    $json["response_code"] = "200";

                    // Create the response object and initialize it
    	        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }

                catch (Exception $e) {
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["message"] = "An error occured while deleting data from the database.";

    	        	// Create the response object and initialize it
    	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/revoke-facebook")
     * @Template()
     */
    public function userRevokeFacebookAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/revoke-facebook"
    	);

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

    	// Get the user
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('GET')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a GET request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($user) || ($user == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This user ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Get his Facebook details
            $facebook_details = $em->getRepository('StrimeAPIUserBundle:UserFacebook')->findOneBy(array("user" => $user));

            // If we got a result, we delete it
            if($facebook_details != NULL) {

                try {
                    $em->remove($facebook_details);
                    $em->flush();

                    $json["status"] = "success";
                    $json["response_code"] = "200";

                    // Create the response object and initialize it
    	        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }

                catch (Exception $e) {
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["message"] = "An error occured while deleting data from the database.";

    	        	// Create the response object and initialize it
    	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/user/{secret_id}/revoke-slack")
     * @Template()
     */
    public function userRevokeSlackAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/user/{user_id}/revoke-slack"
    	);

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

    	// Get the user
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $secret_id));

        // If the type of request used is not the one expected.
        if(!$request->isMethod('GET')) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["message"] = "This is not a GET request.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no result for this ID.
        elseif(!is_object($user) || ($user == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This user ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Get his Google details
            $slack_details = $em->getRepository('StrimeAPIUserBundle:UserSlack')->findOneBy(array("user" => $user));

            // If we got a result, we delete it
            if($slack_details != NULL) {

                try {
                    $em->remove($slack_details);
                    $em->flush();

                    $json["status"] = "success";
                    $json["response_code"] = "200";

                    // Create the response object and initialize it
    	        	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }

                catch (Exception $e) {
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["message"] = "An error occured while deleting data from the database.";

    	        	// Create the response object and initialize it
    	        	$response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }
            }
        }

        // Return the results
        return $response;
    }




    /***************************************************************/
    /******************     Private functions     ******************/
    /***************************************************************/


    /**
     * Private function which creates a single string address
     *
     */

    private function getCompleteAddress($address, $address_more, $zip, $city, $state, $country) {

        $complete_address = "";

        if($address != NULL)
            $complete_address .= $address;

        if(($address_more != NULL) && ($address != NULL))
            $complete_address .= ", " . $address_more;
        elseif(($address_more != NULL) && ($address == NULL))
            $complete_address .= $address_more;

        if(($zip != NULL) && (strlen($complete_address) > 0))
            $complete_address .= ", " . $zip;
        elseif(($zip != NULL) && (strlen($complete_address) == 0))
            $complete_address .= $zip;

        if(($city != NULL) && ($zip == NULL) && (strlen($complete_address) > 0))
            $complete_address .= ", " . $city;
        elseif(($city != NULL) && ($zip == NULL) && (strlen($complete_address) == 0))
            $complete_address .= $city;
        elseif(($zip != NULL) && ($zip != NULL))
            $complete_address .= $city;

        if(($country != NULL) && (strlen($complete_address) > 0))
            $complete_address .= ", " . $country;

        return $complete_address;
    }
}
