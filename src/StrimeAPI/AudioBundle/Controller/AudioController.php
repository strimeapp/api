<?php

namespace StrimeAPI\AudioBundle\Controller;

use StrimeAPI\GlobalBundle\Controller\TokenAuthenticatedController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use StrimeAPI\GlobalBundle\Token\TokenGenerator;
use StrimeAPI\GlobalBundle\Auth\HeadersAuthorization;
use Aws\S3\S3Client;
use FFMpeg;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\AudioBundle\Entity\Audio;
use StrimeAPI\AudioBundle\Entity\AudioComment;
use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\AudioBundle\Entity\AudioEncodingJob;
use StrimeAPI\StatsBundle\Entity\AudioEncodingJobTime;

class AudioController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/audios/get")
     * @Template()
     */
    public function getAudiosAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audios/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the audios
        $audios_results = $em->getRepository('StrimeAPIAudioBundle:Audio')->findBy(array(), array('id' => 'DESC'));
        $audios = array();

        // If no offer has been created yet.
        if($audios_results == NULL) {
            $audios = "No audio has been found.";
            $json["results"] = $audios;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($audios_results as $audio) {

            // Get the project details
            $project = new Project;

            if($audio->getProject() == NULL) {
                $project_details = NULL;
            }
            else {
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $audio->getProject()));
                $project_details = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }

            // Set the data that will be returned in the results
            if($project == NULL) {
                $project = "This audio doesn't belong to a project.";
            }

            // Get the number of assets in the project
            $nb_assets_in_project = 0;
            if($project_details != NULL) {

                $project_action = $this->container->get('strime_api.helpers.project_action');
                $project_action->project = $audio->getProject();
                $nb_assets_in_project = $project_action->countAssetsInProject();
            }

            if($audio->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this audio
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIAudioBundle:AudioComment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.audio = :audio')
                ->setParameter('audio', $audio->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Check if the audio has an associated encoding job
            $encoding_job = $em->getRepository('StrimeAPIAudioBundle:AudioEncodingJob')->findOneBy(array('audio' => $audio));

            if( $encoding_job != NULL ) {
                $encoding_job_details = array(
                    "encoding_job_id" => $encoding_job->getSecretId(),
                    "status" => $encoding_job->getStatus(),
                    "started" => $encoding_job->getStarted(),
                );
            }
            else {
                $encoding_job_details = NULL;
            }

            // Set the results
            $audios[] = array(
                "asset_type" => "audio",
                "asset_id" => $audio->getSecretId(),
                "name" => $audio->getName(),
                "description" => $audio->getDescription(),
                "project" => $project_details,
                "user" => array(
                    "user_id" => $audio->getUser()->getSecretId(),
                    "first_name" => $audio->getUser()->getFirstName(),
                    "last_name" => $audio->getUser()->getLastName()
                ),
                "file" => $audio->getS3Url(),
                "thumbnail" => $audio->getS3ThumbnailUrl(),
                "thumbnail_player" => str_replace(".png", "-player.png", $audio->getS3ThumbnailUrl()),
                "size" => $audio->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "encoding_job" => $encoding_job_details,
                "created_at" => $audio->getCreatedAt(),
                "updated_at" => $audio->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $audios;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }

    /**
     * @Route("/audios/project/{project_id}/get")
     * @Template()
     */
    public function getAudiosByProjectAction(Request $request, $project_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/audios/project/{project_id}/get"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the project details
        $project = new Project;
        $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $project_id));

        if($project == NULL) {
        	$json['results'] = "No project has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the audios
		$audios_results = $em->getRepository('StrimeAPIAudioBundle:Audio')->findBy(array('project' => $project), array('id' => 'DESC'));
		$audios = array();

		// If no offer has been created yet.
		if($audios_results == NULL) {
			$audios = "No audio has been found for this project.";
			$json["results"] = $audios;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($audios_results as $audio) {

            // Get the user details
            $user = new User;
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $audio->getUser()));

            // Set the data that will be returned in the results
            if($user == NULL) {
                $user = "The user associated to this audio has been deleted.";
            }

            if($audio->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this audio, if it's not a project
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIAudioBundle:AudioComment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.audio = :audio')
                ->setParameter('audio', $audio->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Check if the audio has an associated encoding job
            $encoding_job = $em->getRepository('StrimeAPIAudioBundle:AudioEncodingJob')->findOneBy(array('audio' => $audio));

            if( is_object($encoding_job) ) {
                $encoding_job_details = array(
                    "encoding_job_id" => $encoding_job->getSecretId(),
                    "status" => $encoding_job->getStatus(),
                    "started" => $encoding_job->getStarted(),
                );
            }
            else {
                $encoding_job_details = NULL;
            }

            // Set the results
			$audios[] = array(
                "asset_type" => "audio",
				"asset_id" => $audio->getSecretId(),
				"name" => $audio->getName(),
                "description" => $audio->getDescription(),
				"project" => array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                ),
                "user" => array(
                    "secret_id" => $user->getSecretId()
                ),
                "file" => $audio->getS3Url(),
                "thumbnail" => $audio->getS3ThumbnailUrl(),
                "thumbnail_player" => str_replace(".png", "-player.png", $audio->getS3ThumbnailUrl()),
                "size" => $audio->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "encoding_job" => $encoding_job_details,
				"created_at" => $audio->getCreatedAt(),
				"updated_at" => $audio->getUpdatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $audios;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }

    /**
     * @Route("/audios/user/{user_id}/get")
     * @Template()
     */
    public function getAudiosByUserAction(Request $request, $user_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audios/user/{user_id}/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        $user = new User;
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        if($user == NULL) {
            $json['results'] = "No user has been found with this ID.";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Get the audios
        $audios_results = $em->getRepository('StrimeAPIAudioBundle:Audio')->findBy(array('user' => $user), array('id' => 'DESC'));
        $audios = array();

        // If no audio has been created yet.
        if($audios_results == NULL) {
            $audios = "No audio has been found for this user.";
            $json["results"] = $audios;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($audios_results as $audio) {

            // Get the project details
            $project = new Project;

            if($audio->getProject() == NULL) {
                $project_details = NULL;
            }
            else {
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $audio->getProject()));
                $project_details = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }

            // Set the data that will be returned in the results
            if($project == NULL) {
                $project = "This audio doesn't belong to a project.";
            }

            if($audio->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this audio
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIAudioBundle:AudioComment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.audio = :audio')
                ->setParameter('audio', $audio->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Get the number of audios in the project
            $nb_assets_in_project = 0;
            if($project_details != NULL) {

                $project_action = $this->container->get('strime_api.helpers.project_action');
                $project_action->project = $audio->getProject();
                $nb_assets_in_project = $project_action->countAssetsInProject();
            }

            // Check if the audio has an associated encoding job
            $encoding_job = $em->getRepository('StrimeAPIAudioBundle:AudioEncodingJob')->findOneBy(array('audio' => $audio));

            if( $encoding_job != NULL ) {
                $encoding_job_details = array(
                    "encoding_job_id" => $encoding_job->getSecretId(),
                    "status" => $encoding_job->getStatus(),
                    "started" => $encoding_job->getStarted(),
                );
            }
            else {
                $encoding_job_details = NULL;
            }

            // Set the results
            $audios[] = array(
                "asset_type" => "audio",
                "asset_id" => $audio->getSecretId(),
                "name" => $audio->getName(),
                "description" => $audio->getDescription(),
                "project" => $project_details,
                "user" => array(
                    "secret_id" => $user->getSecretId()
                ),
                "file" => $audio->getS3Url(),
                "thumbnail" => $audio->getS3ThumbnailUrl(),
                "thumbnail_player" => str_replace(".png", "-player.png", $audio->getS3ThumbnailUrl()),
                "size" => $audio->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "encoding_job" => $encoding_job_details,
                "nb_assets_in_project" => $nb_assets_in_project,
                "created_at" => $audio->getCreatedAt(),
                "updated_at" => $audio->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $audios;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/audio/{secret_id}/get")
     * @Template()
     */
    public function getAudioAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/{audio_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $audio_details = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $secret_id));
        $audio = array();

        // If we get a result
        if($audio_details != NULL) {

            // Get the details of the project to which belongs this video
            $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $audio_details->getProject()));

            // Prepare the project content
            if($project != NULL) {
                $project_content = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }
            else {
                $project_content = array();
            }

            // Get the details of the user who owns the audio
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $audio_details->getUser()));

            // Prepare the user content
            if($user != NULL) {
                $user_content = array(
                    "user_id" => $user->getSecretId(),
                    "first_name" => $user->getFirstName(),
                    "last_name" => $user->getLastName(),
                    "email" => $user->getEmail(),
                    "mail_notification" => $user->getMailNotification(),
                    "locale" => $user->getLocale()
                );
            }
            else {
                $user_content = array();
            }

            // Prepare the contacts details
            $contacts = $audio_details->getContacts();
            if( $contacts != NULL ) {

                $contacts_list = array();

                // Foreach contact, add its email and ID to the result
                foreach ($contacts as $contact) {

                    $contacts_list[] = array(
                        "contact_id" => $contact->getSecretId(),
                        "email" => $contact->getEmail()
                    );
                }
            }
            else {
                $contacts_list = NULL;
            }

            // Prepare all the URLs
            $file_name = basename( $audio_details->getS3Url() );
            $file_name_parts = explode(".", $file_name);
            if(isset($file_name_parts[0])) {
                $file_name_mp3 = $file_name_parts[0] . "-converted.mp3";
                $file_name_webm = $file_name_parts[0] . "-converted.webm";
            }
            $s3_url_mp3 = str_replace($file_name, $file_name_mp3, $audio_details->getS3Url());
            $s3_url_webm = str_replace($file_name, $file_name_webm, $audio_details->getS3Url());

            // Prepare the array containing the results
            $audio = array(
                "asset_type" => "audio",
                "asset_id" => $audio_details->getSecretId(),
                "user" => $user_content,
                "project" => $project_content,
                "name" => $audio_details->getName(),
                "description" => $audio_details->getDescription(),
                "s3_url" => $audio_details->getS3Url(),
                "s3_url_mp3" => $s3_url_mp3,
                "s3_url_webm" => $s3_url_webm,
                "thumbnail" => $audio_details->getS3ThumbnailUrl(),
                "thumbnail_player" => str_replace(".png", "-player.png", $audio_details->getS3ThumbnailUrl()),
                "size" => $audio_details->getSize(),
                "duration" => $audio_details->getDuration(),
                "contacts" => $contacts_list,
                "created_at" => $audio_details->getCreatedAt(),
                "updated_at" => $audio_details->getUpdatedAt()
            );

            // Add the results to the response
            $json["results"] = $audio;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $audio = "No audio has been found with this ID.";
            $json["results"] = $audio;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/audio/add")
     * @Template()
     */
    public function addAudioAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/add"
        );

        // Get the entity manager
        $em = $this->getDoctrine()->getManager();

        // Get the data
        $user_id = $request->request->get('user_id', NULL);
        $project_id = $request->request->get('project_id', NULL);
        $name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);
        $password = $request->request->get('password', NULL);
        $file = $request->files->get('file', NULL);

        // Get the user details
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // Get the project details
        $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $project_id));

        // If no user has been found with this ID
        if($user == NULL) {
            $json['authorization'] = "No user has been found with this ID.";
            $json["error_source"] = "no_user_found";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If no project has been found with this ID
        if(($project == NULL) && ($project_id != NULL)) {
            $json['authorization'] = "No project has been found with this ID.";
            $json["error_source"] = "no_project_found";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If the type of request used is not the one expected.
        if(!$request->isMethod('POST')) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "405";
            $json["error_message"] = "This is not a POST request.";
            $json["error_source"] = "not_post_request";

            // Create the response object and initialize it
            return new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If some data are missing
        elseif(($user_id == NULL) || ($name == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "Some data are missing.";
            $json["error_source"] = "missing_data";

            // Create the response object and initialize it
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If some data are missing
        elseif($file == NULL) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "Error while sending the file.";
            $json["error_source"] = "file_error";
            $json["file"] = $request->files;

            // Create the response object and initialize it
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // We make sure that the upload happened properly.
        elseif((!$file instanceof UploadedFile) || ($file->getError() != 0)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "We have not been able to process the upload. Make sure that you have been following the guidelines with regards to the format and size of your file.";
            $json["error_source"] = "upload_failed";

            // Create the response object and initialize it
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If everything is fine in this request
        else {

            // Prepare the entities
            $audio = new Audio;
            $encoding_job = new AudioEncodingJob;

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $secret_id));
            }

            // We generate a secret_id for the audio_encoding_job
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $encoding_job_secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIAudioBundle:AudioEncodingJob')->findOneBy(array('secret_id' => $encoding_job_secret_id));
            }

            // We create the audio
            try {
                // Create the entity
                $audio->setSecretId($secret_id);
                $audio->setName($name);
                $audio->setUser($user);
                $audio->setProject($project);
                $audio->setPassword($password);
                $audio->setFile($file);

                if($description != NULL) {
                    $audio->setDescription($description);
                }

                $em->persist($audio);
                $em->flush();

                // Define which server to use for the encoding
                $load_balancing = $this->container->get('strime_api.helpers.load_balancing');
                $encoding_server = $load_balancing->getEncodingServer();

                // Create the encoding job
                $encoding_job->setSecretId($encoding_job_secret_id);
                $encoding_job->setUser($user);
                $encoding_job->setAudio($audio);
                $encoding_job->setProject($audio->getProject());
                $encoding_job->setEncodingServer($encoding_server);
                $encoding_job->setRestartTime(time());
                $em->persist($encoding_job);
                $em->flush();

                // Set the full path of the audio
                $audio_full_path = realpath( __DIR__ . '/../../../../web/' . $audio->getAudio() );

                // Check if the size of the audio doesn't exceed the amount of space remaining for this user
                $filesize = filesize( $audio->getAudio() );
                $total_amount_space_used_by_user = $user->getStorageUsed() + $filesize;
                $storage_multiplier = $this->container->getParameter('storage_multiplier');

                if($total_amount_space_used_by_user > ($user->getOffer()->getStorageAllowed() * $storage_multiplier)) {

                    // We delete the audio
                    unlink( $audio_full_path );
                    $em->remove($audio);
                    $em->flush();

                    $json["status"] = "error";
                    $json["response_code"] = "400";
                    $json["error_message"] = "You are exceeding the amount of space allocated with your current plan. Please upgrade to upload this audio.";
                    $json["error_source"] = "no_more_space_for_user";
                    $json["storage_allowed"] = $user->getOffer()->getStorageAllowed();
                    $json["storage_used"] = $user->getStorageUsed();
                    $json["filesize"] = $filesize;

                    // Create the response object and initialize it
                    return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                    exit;
                }
                else {
                    // We update the storage used
                    $user->setStorageUsed( $total_amount_space_used_by_user );
                    $em->persist($user);

                    // Get the duration of the audio
                    $logger = $this->container->get('logger');
                    $ffprobe = FFMpeg\FFProbe::create([
                        'ffmpeg.binaries'  => $this->container->getParameter('ffmpeg_ffmpeg_path'), // the path to the FFMpeg binary
                        'ffprobe.binaries' => $this->container->getParameter('ffmpeg_ffprobe_path'), // the path to the FFProbe binary
                        'timeout'          => $this->container->getParameter('ffmpeg_timeout'), // the timeout for the underlying process
                        'ffmpeg.threads'   => $this->container->getParameter('ffmpeg_threads'),   // the number of threads that FFMpeg should use
                    ], $logger);
                    $duration = (float)$ffprobe->format( $audio->getAudio() )->get('duration');

                    // Update the DB with the size of the file
                    $audio->setSize($filesize);
                    $audio->setDuration($duration);
                    $em->persist($audio);
                    $em->flush();
                }

                // Get the uploads absolute path
                $uploads_path = realpath( __DIR__.'/../../../../web/' ) . '/';

                // Get the user secret ID
                $user_id = $user->getSecretId();


                // Now, we send the file to the encoding API
                // Get the mime type of the audio
                $audio_name = $file = basename($audio->getAudio());

                // Prepare the parameters
                if($project != NULL)
                    $project_id = $project->getSecretId();
                else
                    $project_id = NULL;

                // Prepare the variables
                $strime_encoding_api_url = $encoding_server;
                $strime_encoding_api_token = $this->container->getParameter('strime_encoding_api_token');
                $endpoint = $strime_encoding_api_url."audio/encode";

                // Set the headers
                $headers = array(
                    'X-Auth-Token' => $strime_encoding_api_token,
                );

                $client = new \GuzzleHttp\Client();
                $json_response = $client->request('POST', $endpoint, [
                    'headers' => $headers,
                    'http_errors' => false,
                    'multipart' => [
                        [
                            'name' => 'encoding_job_id',
                            'contents' => $encoding_job->getSecretId(),
                        ],
                        [
                            'name' => 'user_id',
                            'contents' => $user_id,
                        ],
                        [
                            'name' => 'audio_id',
                            'contents' => $audio->getSecretId(),
                        ],
                        [
                            'name' => 'project_id',
                            'contents' => $project_id,
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($audio_full_path, 'r'),
                            'filename' => $audio_name
                        ]
                    ]
                ]);

                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                $logger = $this->get('logger');
                $logger->info("Encoding: ".var_export($response, TRUE));

                // If the encoding was properly processed
                if(is_object($response) && ($response->{'response_code'} == 201)) {

                    // Get the file size
                    $audio_size = $response->{'audio_filesize'};

                    // Delete the files locally
                    unlink($audio->getAudio());

                    // Update the DB with the size of the file
                    $audio->setSize($audio_size);
                    $em->persist($audio);
                    $em->flush();
                }

                // If an error occured during the encoding process
                else {

                    // We delete the audio and the encoding job in the database
                    $em->remove($audio);
                    $em->remove($encoding_job);
                    $em->flush();

                    // Send the response
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["message"] = "An error occured while encoding the video.";
                    $json["error_source"] = "error_while_encoding_audio";

                    return new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                    exit;
                }

                // Prepare the response
                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["audio"] = array(
                    "asset_type" => "audio",
                    "asset_id" => $audio->getSecretId(),
                    "name" => $audio->getName(),
                    "thumbnail" => $audio->getS3ThumbnailUrl(),
                    "thumbnail_player" => str_replace(".png", "-player.png", $audio->getS3ThumbnailUrl()),
                    "created_at" => $audio->getCreatedAt()->getTimestamp()
                );
                $json["encoding_job"] = array(
                    "encoding_job_id" => $encoding_job->getSecretId()
                );
                if($audio->getProject() != NULL) {
                    $json["project"] = array(
                        "project_id" => $audio->getProject()->getSecretId(),
                        "name" => $audio->getProject()->getName(),
                    );
                }
                else {
                    $json["project"] = NULL;
                }

                // Create the response object and initialize it
                $response = new JsonResponse($json, 201, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {

                // Prepare the response
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["message"] = "An error occured while inserting data into the database.";
                $json["error_source"] = "error_while_inserting_data_in_DB";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/audio/{secret_id}/edit")
     * @Template()
     */
    public function editAudioAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/{audio_id}/edit"
        );

        // Get the data
        $name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);
        $empty_description = $request->request->get('empty_description', 0);
        $project_id = $request->request->get('project_id', NULL);
        $password = $request->request->get('password', NULL);
        $contacts = $request->request->get('contacts', NULL);
        $s3_https_url = $request->request->get('s3_https_url', NULL);
        $s3_https_url_thumbnail = $request->request->get('s3_https_url_thumbnail', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $audio = new Audio;

        // Get the audio
        $audio = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $secret_id));

        // Get the project details
        if(($project_id != NULL) && (strcmp($project_id, "reset") != 0)) {
            $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $project_id));
        }
        else {
            $project = NULL;
        }

        // If no project has been found with this ID
        if(($project == NULL) && ($project_id != NULL) && (strcmp($project_id, "reset") != 0)) {
            $json['authorization'] = "No project has been found with this ID.";
            $json["error_source"] = "no_project_found";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

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
        elseif(!is_object($audio) || ($audio == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This audio ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the audio
            try {
                if($name != NULL)
                    $audio->setName($name);

                if(($description != NULL) && ($empty_description == 0)) {
                    $audio->setDescription($description);
                }
                elseif($empty_description == 1) {
                    $audio->setDescription(NULL);
                }

                if($project_id != NULL) {

                    // Keep the old project in a variable for AWS
                    if($audio->getProject() != NULL)
                        $old_project_id = $audio->getProject()->getSecretId();
                    else
                        $old_project_id = NULL;

                    // Set the new project
                    $audio->setProject($project);
                }

                if($password != NULL)
                    $audio->setPassword($password);

                // Edit the contacts if needed
                $current_contacts = $audio->getContacts();

                if($contacts != NULL) {
                    foreach ($contacts as $contact_id) {
                        $contact = new Contact;
                        $contact = $em->getRepository('StrimeAPIUserBundle:Contact')->findOneBy(array('secret_id' => $contact_id));

                        if($contact != NULL) {
                            $contact_already_exists = FALSE;

                            foreach ($current_contacts as $current_contact) {
                                if($current_contact->getId() == $contact->getId()) {
                                    $contact_already_exists = TRUE;
                                }
                            }

                            if(!$contact_already_exists) {
                                $audio->addContact($contact);
                            }
                        }
                    }
                }


                if($s3_https_url != NULL)
                    $audio->setS3Url($s3_https_url);

                if($s3_https_url_thumbnail != NULL)
                    $audio->setS3ThumbnailUrl($s3_https_url_thumbnail);

                $em->persist($audio);
                $em->flush();


                // If the project is changed, move the files on AWS
                if($project_id != NULL) {

                    // Move the thumbnail and the files of the audio
                    $audio_action = $this->container->get('strime_api.helpers.audio_action');
                    $audio_action->aws_key = $this->container->getParameter('aws_key');
                    $audio_action->aws_secret = $this->container->getParameter('aws_secret');
                    $audio_action->aws_region = $this->container->getParameter('aws_region');
                    $audio_action->aws_bucket = $this->container->getParameter('aws_bucket_audios');
                    $audio_action->old_project_id = $old_project_id;
                    $audio_action->audio = $audio;
                    $audio_action->moveAudioOnAmazon();
                }

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
     * @Route("/audio/{secret_id}/delete")
     * @Template()
     */
    public function deleteAudioAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/audio/{audio_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $audio = new Audio;

        // Get the audio
        $audio = $em->getRepository('StrimeAPIAudioBundle:Audio')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($audio) || ($audio == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_source"] = "audio_doesnt_exist";
            $json["error_message"] = "This audio ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Set the object
            $audio_action = $this->container->get('strime_api.helpers.audio_action');
            $audio_action->aws_key = $this->container->getParameter('aws_key');
            $audio_action->aws_secret = $this->container->getParameter('aws_secret');
            $audio_action->aws_region = $this->container->getParameter('aws_region');
            $audio_action->aws_bucket = $this->container->getParameter('aws_bucket_audios');
            $audio_action->audio = $audio;

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

                // Then we delete the audio itself
                $em->remove($audio);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "204";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 204, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_deleting_image";
                $json["message"] = "An error occured while deleting data from the database.";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }
}
