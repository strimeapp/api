<?php

namespace StrimeAPI\AudioBundle\Controller;

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
use StrimeAPI\AudioBundle\Entity\Audio;
use StrimeAPI\AudioBundle\Entity\AudioComment;

class AudioCommentController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/audio/comments/{audio_id}/get")
     * @Template()
     */
    public function getCommentsByAudioAction(Request $request, $audio_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/audio/comments/{audio_id}/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the audio details
        $audio = new Audio;
        $audio = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $audio_id));

        if($audio == NULL) {
        	$json['authorization'] = "No audio has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the comments
		$comments_results = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findByAudio($audio);
		$comments = array();

		// If no comment has been created yet.
		if($comments_results == NULL) {
			$json["results"] = "No comment has been found for this audio.";
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

            // Get the details of the parent audio
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
                "done" => $comment->getDone(),
				"audio" => array(
					"audio_id" => $audio->getSecretId(),
					"name" => $audio->getName(),
                    "thumbnail" => $audio->getS3ThumbnailUrl()
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
     * @Route("/audio/comment/{secret_id}/get")
     * @Template()
     */
    public function getAudioCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/comment/{comment_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $comment_details = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findOneBy(array('secret_id' => $secret_id));
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

            // Get the details of the parent audio
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
                "done" => $comment_details->getDone(),
                "answer_to" => $answer_to,
				"audio" => array(
					"audio_id" => $comment_details->getAudio()->getSecretId(),
					"name" => $comment_details->getAudio()->getName(),
                    "thumbnail" => $comment_details->getAudio()->getS3ThumbnailUrl()
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
     * @Route("/audio/comment/add")
     * @Template()
     */
    public function addAudioCommentAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/comment/add"
        );

        // Get the data
        $user_id = $request->request->get('user_id', NULL);
        $contact_id = $request->request->get('contact_id', NULL);
        $audio_id = $request->request->get('audio_id', NULL);
        $is_global = $request->request->get('is_global', NULL);
        $comment_content = $request->request->get('comment', NULL);
        $time = $request->request->get('time', NULL);
        $answer_to = $request->request->get('answer_to', NULL);

        // Get the contact details
        $em = $this->getDoctrine()->getManager();
        $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $contact_id));

        // Get the user details
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // Get the original comment details
        $parent_comment = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findOneBy(array('secret_id' => $answer_to));

        // If no contact has been found with this contact ID
        /*if(($contact == NULL) && ($user == NULL)) {
            $json['authorization'] = "No contact or user has been found with this ID.";
            return new JsonResponse($json, 400);
            exit;
        }*/

        // Get the audio details
        $em = $this->getDoctrine()->getManager();
        $audio = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $audio_id));

        // If no audio has been found with this audio ID
        if($audio == NULL) {
            $json['authorization'] = "No audio has been found with this ID.";
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
        elseif($comment_content == NULL) {

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
            $comment = new AudioComment;

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findOneBy(array('secret_id' => $secret_id));
            }

            // We create the comment
            try {
                $comment->setSecretId($secret_id);
                $comment->setAudio($audio);
                $comment->setComment($comment_content);

                if($contact != NULL)
                    $comment->setContact($contact);

                if($user != NULL)
                    $comment->setUser($user);

                if($is_global != NULL)
                    $comment->setIsGlobal($is_global);

                if($time != NULL)
                    $comment->setTime($time);

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
     * @Route("/audio/comment/{secret_id}/edit")
     * @Template()
     */
    public function editAudioCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/comment/{comment_id}/edit"
        );

        $contact_id = $request->request->get('contact_id', NULL);
        $audio_id = $request->request->get('audio_id', NULL);
        $is_global = $request->request->get('is_global', NULL);
        $comment_content = $request->request->get('comment', NULL);
        $time = $request->request->get('time', NULL);
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

        // Get the audio details
        if($audio_id != NULL) {
            $em = $this->getDoctrine()->getManager();
            $audio = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $audio_id));

            // If no audio has been found with this audio ID
            if($audio == NULL) {
                $json['authorization'] = "No audio has been found with this ID.";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                exit;
            }
        }
        else {
            $audio = NULL;
        }

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $comment = new AudioComment;

        // Get the comment
        $comment = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findOneBy(array('secret_id' => $secret_id));

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

                if($audio != NULL)
                    $comment->setAudio($audio);

                if($comment_content != NULL)
                    $comment->setComment($comment_content);

                if($is_global != NULL)
                    $comment->setIsGlobal($is_global);

                if($time != NULL)
                    $comment->setTime($time);

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
     * @Route("/audio/comment/{secret_id}/delete")
     * @Template()
     */
    public function deleteAudioCommentAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/comment/{comment_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $comment = new AudioComment;

        // Get the comment
        $comment = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findOneBy(array('secret_id' => $secret_id));

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
            $childs = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findBy(array('answer_to' => $comment));

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
     * @Route("/audio/comments/{audio_id}/count")
     * @Template()
     */
    public function countCommentsByAudioAction(Request $request, $audio_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/comments/{audio_id}/count"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the audio details
        $audio = new AudioComment;
        $audio = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $audio_id));

        if($audio == NULL) {
            $json['authorization'] = "No audio has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the comments
        $comments_results = $em->getRepository('StrimeAPIAudioBundle:AudioComment')->findByAudio($audio);
        $comments = array();

        $query = $em->createQueryBuilder();
        $query->select( 'count(api_audio_comment.id)' );
        $query->from( 'StrimeAPIAudioBundle:AudioComment','api_audio_comment' );
        $query->where('api_audio_comment.audio = :audio_id');
        $query->setParameter('audio_id', $audio->getId());
        $nb_comments = $query->getQuery()->getSingleScalarResult();

        // Add the results to the response
        $json["nb_comments"] = $nb_comments;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }
}
