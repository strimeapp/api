<?php

namespace StrimeAPI\VideoBundle\Controller;

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
use StrimeAPI\VideoBundle\Entity\Video;
use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\VideoBundle\Entity\CommentWithThumbInError;

class CommentController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/comments/{video_id}/get")
     * @Template()
     */
    public function getCommentsByVideoAction(Request $request, $video_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/comments/{video_id}/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the video details
        $video = new Video;
        $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video_id));

        if($video == NULL) {
        	$json['authorization'] = "No video has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the comments
		$comments_results = $em->getRepository('StrimeAPIVideoBundle:Comment')->findByVideo($video);
		$comments = array();

		// If no comment has been created yet.
		if($comments_results == NULL) {
			$json["results"] = "No comment has been found for this video.";
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

            // Get the details of the parent video
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
                "time" => $comment->getTime(),
                "area" => $comment->getArea(),
                "done" => $comment->getDone(),
                "s3_url" => $comment->getS3Url(),
				"video" => array(
					"video_id" => $video->getSecretId(),
					"name" => $video->getName()
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
     * @Route("/comment/{secret_id}/get")
     * @Template()
     */
    public function getCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/comment/{comment_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $comment_details = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $secret_id));
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

            // Get the details of the parent video
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
                "time" => $comment_details->getTime(),
                "area" => $comment_details->getArea(),
                "done" => $comment_details->getDone(),
                "s3_url" => $comment_details->getS3Url(),
                "answer_to" => $answer_to,
				"video" => array(
					"video_id" => $comment_details->getVideo()->getSecretId(),
					"name" => $comment_details->getVideo()->getName()
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
     * @Route("/comment/add")
     * @Template()
     */
    public function addCommentAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/comment/add"
        );

        // Get the data
        $user_id = $request->request->get('user_id', NULL);
        $contact_id = $request->request->get('contact_id', NULL);
        $video_id = $request->request->get('video_id', NULL);
        $is_global = $request->request->get('is_global', NULL);
        $comment_content = $request->request->get('comment', NULL);
        $time = $request->request->get('time', NULL);
        $area = $request->request->get('area', NULL);
        $answer_to = $request->request->get('answer_to', NULL);

        // Get the contact details
        $em = $this->getDoctrine()->getManager();
        $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $contact_id));

        // Get the user details
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // Get the original comment details
        $parent_comment = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $answer_to));

        // If no contact has been found with this contact ID
        /*if(($contact == NULL) && ($user == NULL)) {
            $json['authorization'] = "No contact or user has been found with this ID.";
            return new JsonResponse($json, 400);
            exit;
        }*/

        // Get the video details
        $em = $this->getDoctrine()->getManager();
        $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video_id));

        // If no video has been found with this video ID
        if($video == NULL) {
            $json['authorization'] = "No video has been found with this ID.";
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
            $comment = new Comment;

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $secret_id));
            }

            // We create the comment
            try {
                $comment->setSecretId($secret_id);
                $comment->setVideo($video);
                $comment->setComment($comment_content);

                if($contact != NULL)
                    $comment->setContact($contact);

                if($user != NULL)
                    $comment->setUser($user);

                if($is_global != NULL)
                    $comment->setIsGlobal($is_global);

                if($time != NULL)
                    $comment->setTime($time);

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
     * @Route("/comment/{secret_id}/edit")
     * @Template()
     */
    public function editCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/comment/{comment_id}/edit"
        );

        $contact_id = $request->request->get('contact_id', NULL);
        $video_id = $request->request->get('video_id', NULL);
        $is_global = $request->request->get('is_global', NULL);
        $comment_content = $request->request->get('comment', NULL);
        $time = $request->request->get('time', NULL);
        $area = $request->request->get('area', NULL);
        $done = $request->request->get('done', NULL);
        $s3_url = $request->request->get('s3_url', NULL);

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

        // Get the video details
        if($video_id != NULL) {
            $em = $this->getDoctrine()->getManager();
            $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video_id));

            // If no video has been found with this video ID
            if($video == NULL) {
                $json['authorization'] = "No video has been found with this ID.";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                exit;
            }
        }
        else {
            $video = NULL;
        }

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $comment = new Comment;

        // Get the comment
        $comment = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $secret_id));

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
            $json["message"] = "This comment ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the project
            try {
                if($contact != NULL)
                    $comment->setContact($contact);

                if($video != NULL)
                    $comment->setVideo($video);

                if($comment_content != NULL)
                    $comment->setComment($comment_content);

                if($is_global != NULL)
                    $comment->setIsGlobal($is_global);

                if($time != NULL)
                    $comment->setTime($time);

                if($area != NULL)
                    $comment->setArea($area);

                if($done != NULL)
                    $comment->setDone($done);

                if($s3_url != NULL)
                    $comment->setS3Url($s3_url);

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
     * @Route("/comment/{secret_id}/delete")
     * @Template()
     */
    public function deleteCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/comment/{comment_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $comment = new Comment;

        // Get the comment
        $comment = $em->getRepository('StrimeAPIVideoBundle:Comment')->findOneBy(array('secret_id' => $secret_id));

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

            // Set the object
            $comment_action = $this->container->get('strime_api.helpers.comment_action');
            $comment_action->aws_key = $this->container->getParameter('aws_key');
            $comment_action->aws_secret = $this->container->getParameter('aws_secret');
            $comment_action->aws_region = $this->container->getParameter('aws_region');
            $comment_action->aws_bucket = $this->container->getParameter('aws_bucket_comments');

            // Find the child comments of this comment
            $childs = $em->getRepository('StrimeAPIVideoBundle:Comment')->findBy(array('answer_to' => $comment));

            // Delete these childs
            foreach ($childs as $child) {

                // Remove the files from Amazon first
                $comment_action->comment = $child;

                // Delete the file on Amazon
                $comment_action->deleteCommentScreenshotFromAmazon();

                // Delete the comment from the errors table
                $comment_action->deleteCommentFromErrorsTable();

                $em->remove($child);
                $em->flush();
            }

            // We delete the comment
            try {

                // Remove the files from Amazon first
                $comment_action->comment = $comment;

                // Delete the file on Amazon
                $comment_action->deleteCommentScreenshotFromAmazon();

                // Delete the comment from the errors table
                $comment_action->deleteCommentFromErrorsTable();

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
     * @Route("/comments/{video_id}/count")
     * @Template()
     */
    public function countCommentsByVideoAction(Request $request, $video_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/comments/{video_id}/count"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the video details
        $video = new Video;
        $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video_id));

        if($video == NULL) {
            $json['authorization'] = "No video has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the comments
        $comments_results = $em->getRepository('StrimeAPIVideoBundle:Comment')->findByVideo($video);
        $comments = array();

        $query = $em->createQueryBuilder();
        $query->select( 'count(api_comment.id)' );
        $query->from( 'StrimeAPIVideoBundle:Comment','api_comment' );
        $query->where('api_comment.video = :video_id');
        $query->setParameter('video_id', $video->getId());
        $nb_comments = $query->getQuery()->getSingleScalarResult();

        // Add the results to the response
        $json["nb_comments"] = $nb_comments;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }
}
