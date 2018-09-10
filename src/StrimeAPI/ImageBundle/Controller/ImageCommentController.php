<?php

namespace StrimeAPI\ImageBundle\Controller;

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
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\ImageBundle\Entity\Image;
use StrimeAPI\ImageBundle\Entity\ImageComment;

class ImageCommentController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/image/comments/{image_id}/get")
     * @Template()
     */
    public function getCommentsByImageAction(Request $request, $image_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/image/comments/{image_id}/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the image details
        $image = new Image;
        $image = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $image_id));

        if($image == NULL) {
        	$json['authorization'] = "No image has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the comments
		$comments_results = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findByImage($image);
		$comments = array();

		// If no comment has been created yet.
		if($comments_results == NULL) {
			$json["results"] = "No comment has been found for this image.";
            $json["nb_comments"] = 0;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($comments_results as $comment) {

            // Get the details of the author
            if($comment->getContact() != NULL) {
                $author["author_id"] = $comment->getContact()->getSecretId();
                $author["author_type"] = "contact";
                $author["name"] = $comment->getContact()->getEmail();
                $author["email"] = $comment->getContact()->getEmail();
                $author["avatar"] = NULL;
            }
            elseif($comment->getUser() != NULL) {
                $author["author_id"] = $comment->getUser()->getSecretId();
                $author["author_type"] = "user";
                $author["name"] = $comment->getUser()->getFullName();
                $author["email"] = $comment->getUser()->getEmail();
                $author["avatar"] = $comment->getUser()->getAvatar();
            }
            else {
                $author = NULL;
            }

            // Get the details of the parent image
            if($comment->getAnswerTo() != NULL) {
                $answer_to = $comment->getAnswerTo()->getSecretId();
            }
            else {
                $answer_to = NULL;
            }

			$comments[] = array(
				"comment_id" => $comment->getSecretId(),
				"is_global" => $comment->getIsGlobal(),
				"comment" => $comment->getComment(),
                "area" => $comment->getArea(),
                "done" => $comment->getDone(),
				"image" => array(
					"image_id" => $image->getSecretId(),
					"name" => $image->getName(),
                    "thumbnail" => $image->getS3ThumbnailUrl()
				),
                "author" => $author,
                "answer_to" => $answer_to,
				"created_at" => $comment->getCreatedAt(),
				"updated_at" => $comment->getUpdatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $comments;
        $json["nb_comments"] = count($comments);
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/image/comment/{secret_id}/get")
     * @Template()
     */
    public function getImageCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/comment/{comment_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $comment_details = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findOneBy(array('secret_id' => $secret_id));
        $comment = array();

        // If we get a result
        if($comment_details != NULL) {

            // Get the details of the contact who owns the comment
            $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('id' => $comment_details->getContact()));

            // Get the details of the author
            if($comment_details->getContact() != NULL) {
                $author["author_id"] = $comment_details->getContact()->getSecretId();
                $author["author_type"] = "contact";
                $author["name"] = $comment_details->getContact()->getEmail();
                $author["email"] = $comment_details->getContact()->getEmail();
                $author["avatar"] = NULL;
            }
            elseif($comment_details->getUser() != NULL) {
                $author["author_id"] = $comment_details->getUser()->getSecretId();
                $author["author_type"] = "user";
                $author["name"] = $comment_details->getUser()->getFullName();
                $author["email"] = $comment_details->getUser()->getEmail();
                $author["avatar"] = $comment_details->getUser()->getAvatar();
            }
            else {
                $author = NULL;
            }

            // Get the details of the parent image
            if($comment_details->getAnswerTo() != NULL) {
                $answer_to = $comment_details->getAnswerTo()->getSecretId();
            }
            else {
                $answer_to = NULL;
            }

            // Prepare the array containing the results
            $comment = array(
                "comment_id" => $comment_details->getSecretId(),
                "author" => $author,
                "is_global" => $comment_details->getIsGlobal(),
                "comment" => $comment_details->getComment(),
                "area" => $comment_details->getArea(),
                "done" => $comment_details->getDone(),
                "answer_to" => $answer_to,
				"image" => array(
					"image_id" => $comment_details->getImage()->getSecretId(),
					"name" => $comment_details->getImage()->getName(),
                    "thumbnail" => $comment_details->getImage()->getS3ThumbnailUrl()
				),
                "created_at" => $comment_details->getCreatedAt(),
                "updated_at" => $comment_details->getUpdatedAt()
            );

            // Add the results to the response
            $json["results"] = $comment;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $comment = "No comment has been found with this ID.";
            $json["results"] = $comment;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/image/comment/add")
     * @Template()
     */
    public function addImageCommentAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/comment/add"
        );

        // Get the data
        $user_id = $request->request->get('user_id', NULL);
        $contact_id = $request->request->get('contact_id', NULL);
        $image_id = $request->request->get('image_id', NULL);
        $is_global = $request->request->get('is_global', NULL);
        $comment_content = $request->request->get('comment', NULL);
        $area = $request->request->get('area', NULL);
        $answer_to = $request->request->get('answer_to', NULL);

        // Get the contact details
        $em = $this->getDoctrine()->getManager();
        $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $contact_id));

        // Get the user details
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // Get the original comment details
        $parent_comment = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findOneBy(array('secret_id' => $answer_to));

        // If no contact has been found with this contact ID
        /*if(($contact == NULL) && ($user == NULL)) {
            $json['authorization'] = "No contact or user has been found with this ID.";
            return new JsonResponse($json, 400);
            exit;
        }*/

        // Get the image details
        $em = $this->getDoctrine()->getManager();
        $image = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $image_id));

        // If no image has been found with this image ID
        if($image == NULL) {
            $json['authorization'] = "No image has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
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
        elseif(($area == NULL) || ($comment_content == NULL)) {

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
            $comment = new ImageComment;

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findOneBy(array('secret_id' => $secret_id));
            }

            // We create the comment
            try {
                $comment->setSecretId($secret_id);
                $comment->setImage($image);
                $comment->setComment($comment_content);

                if($contact != NULL)
                    $comment->setContact($contact);

                if($user != NULL)
                    $comment->setUser($user);

                if($is_global != NULL)
                    $comment->setIsGlobal($is_global);

                if($area != NULL)
                    $comment->setArea($area);

                if($answer_to != NULL)
                    $comment->setAnswerTo($parent_comment);

                $em->persist($comment);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["comment_id"] = $comment->getSecretId();

                if($user != NULL)
                    $json["user"] = $user->getFullName();
                elseif($contact != NULL)
                    $json["user"] = $contact->getEmail();
                else
                    $json["user"] = NULL;

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
     * @Route("/image/comment/{secret_id}/edit")
     * @Template()
     */
    public function editImageCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/comment/{comment_id}/edit"
        );

        $contact_id = $request->request->get('contact_id', NULL);
        $image_id = $request->request->get('image_id', NULL);
        $is_global = $request->request->get('is_global', NULL);
        $comment_content = $request->request->get('comment', NULL);
        $area = $request->request->get('area', NULL);
        $done = $request->request->get('done', NULL);

        // Get the contact details
        if($contact_id != NULL) {
            $em = $this->getDoctrine()->getManager();
            $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('id' => $contact_id));

            // If no contact has been found with this contact ID
            if($contact == NULL) {
                $json['authorization'] = "No contact has been found with this ID.";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                exit;
            }
        }
        else {
            $contact = NULL;
        }

        // Get the image details
        if($image_id != NULL) {
            $em = $this->getDoctrine()->getManager();
            $image = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $image_id));

            // If no image has been found with this image ID
            if($image == NULL) {
                $json['authorization'] = "No image has been found with this ID.";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                exit;
            }
        }
        else {
            $image = NULL;
        }

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $comment = new ImageComment;

        // Get the comment
        $comment = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($comment) || ($comment == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This project ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the project
            try {
                if($contact != NULL)
                    $comment->setContact($contact);

                if($image != NULL)
                    $comment->setImage($image);

                if($comment_content != NULL)
                    $comment->setComment($comment_content);

                if($is_global != NULL)
                    $comment->setIsGlobal($is_global);

                if($area != NULL)
                    $comment->setArea($area);

                if($done != NULL)
                    $comment->setDone($done);

                $em->persist($comment);
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
     * @Route("/image/comment/{secret_id}/delete")
     * @Template()
     */
    public function deleteImageCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/comment/{comment_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $comment = new ImageComment;

        // Get the comment
        $comment = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($comment) || ($comment == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "This comment ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Find the child comments of this comment
            $childs = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findBy(array('answer_to' => $comment));

            // Delete these childs
            foreach ($childs as $child) {

                $em->remove($child);
                $em->flush();
            }

            // We delete the comment
            try {

                $em->remove($comment);
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
     * @Route("/image/comments/{image_id}/count")
     * @Template()
     */
    public function countCommentsByImageAction(Request $request, $image_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/comments/{image_id}/count"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the image details
        $image = new ImageComment;
        $image = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $image_id));

        if($image == NULL) {
            $json['authorization'] = "No image has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the comments
        $comments_results = $em->getRepository('StrimeAPIImageBundle:ImageComment')->findByImage($image);
        $comments = array();

        $query = $em->createQueryBuilder();
        $query->select( 'count(api_image_comment.id)' );
        $query->from( 'StrimeAPIImageBundle:ImageComment','api_image_comment' );
        $query->where('api_image_comment.image = :image_id');
        $query->setParameter('image_id', $image->getId());
        $nb_comments = $query->getQuery()->getSingleScalarResult();

        // Add the results to the response
        $json["nb_comments"] = $nb_comments;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }
}
