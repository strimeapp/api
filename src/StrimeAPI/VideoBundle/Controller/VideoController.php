<?php

namespace StrimeAPI\VideoBundle\Controller;

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
use StrimeAPI\VideoBundle\Helpers\VideoAction;
use Aws\S3\S3Client;
use FFMpeg;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\VideoBundle\Entity\Video;
use StrimeAPI\VideoBundle\Entity\VideoYoutube;
use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\VideoBundle\Entity\EncodingJob;
use StrimeAPI\StatsBundle\Entity\EncodingJobTime;

class VideoController extends FOSRestController implements TokenAuthenticatedController
{

    /**
     * @Route("/videos/get")
     * @Template()
     */
    public function getVideosAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/videos/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the videos
        $videos_results = $em->getRepository('StrimeAPIVideoBundle:Video')->findBy(array(), array('id' => 'DESC'));
        $videos = array();

        // If no offer has been created yet.
        if($videos_results == NULL) {
            $videos = "No video has been found.";
            $json["results"] = $videos;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($videos_results as $video) {

            // Get the project details
            $project = new Project;

            if($video->getProject() == NULL) {
                $project_details = NULL;
            }
            else {
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $video->getProject()));
                $project_details = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }

            // Set the data that will be returned in the results
            if($project == NULL) {
                $project = "This video doesn't belong to a project.";
            }

            if($video->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this video
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIVideoBundle:Comment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.video = :video')
                ->setParameter('video', $video->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Check if the video has an associated encoding job
            $encoding_job = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('video' => $video));

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

            // Check if the video has been shared on Youtube
            $video_youtube = $em->getRepository('StrimeAPIVideoBundle:VideoYoutube')->findOneBy(array('video' => $video));

            // Set the variable with the result
            if($video_youtube != NULL) {
                $video_youtube_details = array(
                    "youtube_id" => $video_youtube->getYoutubeId()
                );
            }
            else {
                $video_youtube_details = NULL;
            }

            // Set the results
            $videos[] = array(
                "asset_type" => "video",
                "asset_id" => $video->getSecretId(),
                "name" => $video->getName(),
                "description" => $video->getDescription(),
                "project" => $project_details,
                "user" => array(
                    "user_id" => $video->getUser()->getSecretId(),
                    "first_name" => $video->getUser()->getFirstName(),
                    "last_name" => $video->getUser()->getLastName()
                ),
                "file" => $video->getS3Url(),
                "thumbnail" => $video->getS3ScreenshotUrl(),
                "size" => $video->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "encoding_job" => $encoding_job_details,
                "video_youtube_details" => $video_youtube_details,
                "created_at" => $video->getCreatedAt(),
                "updated_at" => $video->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $videos;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }

    /**
     * @Route("/videos/project/{project_id}/get")
     * @Template()
     */
    public function getVideosByProjectAction(Request $request, $project_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/videos/project/{project_id}/get"
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

        // Get the videos
		$videos_results = $em->getRepository('StrimeAPIVideoBundle:Video')->findBy(array('project' => $project), array('id' => 'DESC'));
		$videos = array();

		// If no offer has been created yet.
		if($videos_results == NULL) {
			$videos = "No video has been found for this project.";
			$json["results"] = $videos;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($videos_results as $video) {

            // Get the user details
            $user = new User;
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $video->getUser()));

            // Set the data that will be returned in the results
            if($user == NULL) {
                $user = "The user associated to this video has been deleted.";
            }

            if($video->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this video, if it's not a project
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIVideoBundle:Comment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.video = :video')
                ->setParameter('video', $video->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Check if the video has an associated encoding job
            $encoding_job = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('video' => $video));

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

            // Check if the video has been shared on Youtube
            $video_youtube = $em->getRepository('StrimeAPIVideoBundle:VideoYoutube')->findOneBy(array('video' => $video));

            // Set the variable with the result
            if($video_youtube != NULL) {
                $video_youtube_details = array(
                    "youtube_id" => $video_youtube->getYoutubeId()
                );
            }
            else {
                $video_youtube_details = NULL;
            }

            // Set the results
			$videos[] = array(
                "asset_type" => "video",
				"asset_id" => $video->getSecretId(),
				"name" => $video->getName(),
                "description" => $video->getDescription(),
				"project" => array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                ),
                "user" => array(
                    "secret_id" => $user->getSecretId()
                ),
                "file" => $video->getS3Url(),
                "thumbnail" => $video->getS3ScreenshotUrl(),
                "size" => $video->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "encoding_job" => $encoding_job_details,
                "video_youtube_details" => $video_youtube_details,
				"created_at" => $video->getCreatedAt(),
				"updated_at" => $video->getUpdatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $videos;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }

    /**
     * @Route("/videos/user/{user_id}/get")
     * @Template()
     */
    public function getVideosByUserAction(Request $request, $user_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/videos/user/{user_id}/get"
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

        // Get the videos
        $videos_results = $em->getRepository('StrimeAPIVideoBundle:Video')->findBy(array('user' => $user), array('id' => 'DESC'));
        $videos = array();

        // If no offer has been created yet.
        if($videos_results == NULL) {
            $videos = "No video has been found for this user.";
            $json["results"] = $videos;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($videos_results as $video) {

            // Get the project details
            $project = new Project;

            if($video->getProject() == NULL) {
                $project_details = NULL;
            }
            else {
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $video->getProject()));
                $project_details = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }

            // Set the data that will be returned in the results
            if($project == NULL) {
                $project = "This video doesn't belong to a project.";
            }

            if($video->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this video
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIVideoBundle:Comment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.video = :video')
                ->setParameter('video', $video->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Get the number of assets in the project
            $nb_assets_in_project = 0;
            if($project_details != NULL) {

                $project_action = $this->container->get('strime_api.helpers.project_action');
                $project_action->project = $video->getProject();
                $nb_assets_in_project = $project_action->countAssetsInProject();
            }

            // Check if the video has an associated encoding job
            $encoding_job = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('video' => $video));

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

            // Check if the video has been shared on Youtube
            $video_youtube = $em->getRepository('StrimeAPIVideoBundle:VideoYoutube')->findOneBy(array('video' => $video));

            // Set the variable with the result
            if($video_youtube != NULL) {
                $video_youtube_details = array(
                    "youtube_id" => $video_youtube->getYoutubeId()
                );
            }
            else {
                $video_youtube_details = NULL;
            }

            // Set the results
            $videos[] = array(
                "asset_type" => "video",
                "asset_id" => $video->getSecretId(),
                "name" => $video->getName(),
                "description" => $video->getDescription(),
                "project" => $project_details,
                "user" => array(
                    "secret_id" => $user->getSecretId()
                ),
                "file" => $video->getS3Url(),
                "thumbnail" => $video->getS3ScreenshotUrl(),
                "size" => $video->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "nb_assets_in_project" => $nb_assets_in_project,
                "encoding_job" => $encoding_job_details,
                "video_youtube_details" => $video_youtube_details,
                "created_at" => $video->getCreatedAt(),
                "updated_at" => $video->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $videos;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/video/{secret_id}/get")
     * @Template()
     */
    public function getVideoAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/video/{video_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $video_details = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $secret_id));
        $video = array();

        // If we get a result
        if($video_details != NULL) {

            // Get the details of the project to which belongs this video
            $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $video_details->getProject()));

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

            // Get the details of the user who owns the video
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $video_details->getUser()));

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
            $contacts = $video_details->getContacts();
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

            // Check if the video has been shared on Youtube
            $video_youtube = $em->getRepository('StrimeAPIVideoBundle:VideoYoutube')->findOneBy(array('video' => $video_details));

            // Set the variable with the result
            if($video_youtube != NULL) {
                $video_youtube_details = array(
                    "youtube_id" => $video_youtube->getYoutubeId()
                );
            }
            else {
                $video_youtube_details = NULL;
            }

            // Prepare the array containing the results
            $video = array(
                "asset_type" => "video",
                "asset_id" => $video_details->getSecretId(),
                "user" => $user_content,
                "project" => $project_content,
                "name" => $video_details->getName(),
                "description" => $video_details->getDescription(),
                "s3_url" => $video_details->getS3Url(),
                "thumbnail" => $video_details->getS3ScreenshotUrl(),
                "size" => $video_details->getSize(),
                "contacts" => $contacts_list,
                "video_youtube_details" => $video_youtube_details,
                "created_at" => $video_details->getCreatedAt(),
                "updated_at" => $video_details->getUpdatedAt()
            );

            // Return the MP4 and Webm converted versions
            if(isset($video['s3_url'])) {
                $video_parts = explode(".", $video['s3_url']);
                $video_parts[ count($video_parts) - 2 ] .= "-converted";
                $video_parts[ count($video_parts) - 1 ] = "mp4";
                $video['video_mp4'] = implode(".", $video_parts);
                $video_parts[ count($video_parts) - 1 ] = "webm";
                $video['video_webm'] = implode(".", $video_parts);
            }

            // Add the results to the response
            $json["results"] = $video;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $video = "No video has been found with this ID.";
            $json["results"] = $video;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/video/add")
     * @Template()
     */
    public function addVideoAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/video/add"
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

        // Count the number of videos of the user
        $query = $em->createQueryBuilder();
        $query->select( 'count(api_video.id)' );
        $query->from( 'StrimeAPIVideoBundle:Video','api_video' );
        $query->where('api_video.user = :user_id');
        $query->setParameter('user_id', $user->getId());
        $nb_videos = $query->getQuery()->getSingleScalarResult();

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

        // Check if the user has not reached the max number of videos
        elseif(($user->getOffer()->getNbVideos() <= $nb_videos) && ($user->getOffer()->getNbVideos() != 0)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "The user has reached the max number of videos.";
            $json["error_source"] = "max_number_of_videos";

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
            $video = new Video;
            $encoding_job = new EncodingJob;

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $secret_id));
            }

            // We generate a secret_id for the encoding_job
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $encoding_job_secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $encoding_job_secret_id));
            }

            // We create the video
            try {
                // Create the entity
                $video->setSecretId($secret_id);
                $video->setName($name);
                $video->setUser($user);
                $video->setProject($project);
                $video->setPassword($password);
                $video->setFile($file);

                if($description != NULL) {
                    $video->setDescription($description);
                }

                $em->persist($video);
                $em->flush();

                // Define which server to use for the encoding
                $load_balancing = $this->container->get('strime_api.helpers.load_balancing');
                $encoding_server = $load_balancing->getEncodingServer();

                // Create the encoding job
                $encoding_job->setSecretId($encoding_job_secret_id);
                $encoding_job->setUser($user);
                $encoding_job->setVideo($video);
                $encoding_job->setProject($video->getProject());
                $encoding_job->setEncodingServer($encoding_server);
                $encoding_job->setRestartTime(time());
                $em->persist($encoding_job);
                $em->flush();

                // Get the duration of the video
                $logger = $this->container->get('logger');
                $ffprobe = FFMpeg\FFProbe::create([
                    'ffmpeg.binaries'  => $this->container->getParameter('ffmpeg_ffmpeg_path'), // the path to the FFMpeg binary
                    'ffprobe.binaries' => $this->container->getParameter('ffmpeg_ffprobe_path'), // the path to the FFProbe binary
                    'timeout'          => $this->container->getParameter('ffmpeg_timeout'), // the timeout for the underlying process
                    'ffmpeg.threads'   => $this->container->getParameter('ffmpeg_threads'),   // the number of threads that FFMpeg should use
                ], $logger);
                $duration = (float)$ffprobe->format( $video->getVideo() )->get('duration');

                // Update the duration of the video
                $video->setDuration($duration);
                $em->persist($video);
                $em->flush();

                // Check if the size of the video doesn't exceed the amount of space remaining for this user
                $filesize = filesize( $video->getVideo() );
                $total_amount_space_used_by_user = $user->getStorageUsed() + $filesize;
                $storage_multiplier = $this->container->getParameter('storage_multiplier');

                if($total_amount_space_used_by_user > ($user->getOffer()->getStorageAllowed() * $storage_multiplier)) {

                    // We delete the video and the encoding job
                    unlink( $video->getVideo() );
                    $em->remove($video);
                    $em->remove($encoding_job);
                    $em->flush();

                    $json["status"] = "error";
                    $json["response_code"] = "400";
                    $json["error_message"] = "You are exceeding the amount of space allocated with your current plan. Please upgrade to upload this video.";
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
                    $em->flush();
                }

                // Get the uploads absolute path
                $uploads_path = realpath( __DIR__.'/../../../../web/' ) . '/';

                // Get the user secret ID
                $user_id = $user->getSecretId();


                // Now, we send the file to the encoding API
                // Get the mime type of the video
                $video_name = $file = basename($video->getVideo());
                $video_full_path = realpath( __DIR__ . "/../../../../web/" . $video->getVideo() );

                // Prepare the parameters
                if($project != NULL)
                    $project_id = $project->getSecretId();
                else
                    $project_id = NULL;

                // Prepare the variables
                $strime_encoding_api_url = $encoding_server;
                $strime_encoding_api_token = $this->container->getParameter('strime_encoding_api_token');
                $endpoint = $strime_encoding_api_url."video/encode";

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
                            'name' => 'video_id',
                            'contents' => $video->getSecretId(),
                        ],
                        [
                            'name' => 'project_id',
                            'contents' => $project_id,
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($video_full_path, 'r'),
                            'filename' => $video_name
                        ]
                    ]
                ]);

                $curl_status = $json_response->getStatusCode();
                $response = json_decode( $json_response->getBody() );

                // If the encoding was properly processed
                if(is_object($response) && ($response->{'response_code'} == 201)) {

                    // Get the file size
                    $video_size = $response->{'video_filesize'};

                    // Delete the files locally
                    unlink($video->getVideo());

                    // Update the DB with the size of the file
                    $video->setSize($video_size);
                    $em->persist($video);
                    $em->flush();
                }

                // If an error occured during the encoding process
                else {

                    // We delete the video and the encoding job in the database
                    $em->remove($video);
                    $em->remove($encoding_job);
                    $em->flush();

                    // Send the response
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["message"] = "An error occured while uploading the video on Amazon S3.";
                    $json["error_source"] = "error_while_uploading_on_S3";

                    return new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                    exit;
                }

                // Prepare the response
                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["video"] = array(
                    "asset_type" => "video",
                    "asset_id" => $video->getSecretId(),
                    "name" => $video->getName(),
                    "thumbnail" => $video->getS3ScreenshotUrl(),
                    "created_at" => $video->getCreatedAt()->getTimestamp()
                );
                $json["encoding_job"] = array(
                    "encoding_job_id" => $encoding_job->getSecretId()
                );
                if($video->getProject() != NULL) {
                    $json["project"] = array(
                        "project_id" => $video->getProject()->getSecretId(),
                        "name" => $video->getProject()->getName(),
                    );
                }
                else {
                    $json["project"] = NULL;
                }

                // Create the response object and initialize it
                $response = new JsonResponse($json, 201, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {

                // Delete the encoding job from the database
                $em->remove($encoding_job);
                $em->flush();

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
     * @Route("/video/{secret_id}/edit")
     * @Template()
     */
    public function editVideoAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/video/{video_id}/edit"
        );

        // Get the data
        $name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);
        $empty_description = $request->request->get('empty_description', 0);
        $project_id = $request->request->get('project_id', NULL);
        $password = $request->request->get('password', NULL);
        $contacts = $request->request->get('contacts', NULL);
        $s3_https_url = $request->request->get('s3_https_url', NULL);
        $s3_https_url_screenshot = $request->request->get('s3_https_url_screenshot', NULL);
        $video_youtube_id = $request->request->get('video_youtube_id', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $video = new Video;

        // Get the video
        $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($video) || ($video == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This video ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the video
            try {
                if($name != NULL)
                    $video->setName($name);

                if(($description != NULL) && ($empty_description == 0)) {
                    $video->setDescription($description);
                }
                elseif($empty_description == 1) {
                    $video->setDescription(NULL);
                }

                if($project_id != NULL) {

                    // Keep the old project in a variable for AWS
                    if($video->getProject() != NULL)
                        $old_project_id = $video->getProject()->getSecretId();
                    else
                        $old_project_id = NULL;

                    // Set the new project
                    $video->setProject($project);
                }

                if($password != NULL)
                    $video->setPassword($password);

                // Edit the contacts if needed
                $current_contacts = $video->getContacts();

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
                                $video->addContact($contact);
                            }
                        }
                    }
                }


                if($s3_https_url != NULL)
                    $video->setS3Url($s3_https_url);

                if($s3_https_url_screenshot != NULL)
                    $video->setS3ScreenshotUrl($s3_https_url_screenshot);

                $em->persist($video);
                $em->flush();


                // If a Youtube ID has been passed as a parameter
                if($video_youtube_id != NULL) {

                    // Check if there is already a record for this user
                    $video_youtube = $em->getRepository('StrimeAPIVideoBundle:VideoYoutube')->findOneBy(array('video' => $video));

                    // If there is already a record for this user, update the Google ID
                    if($video_youtube != NULL) {
                        if($video_youtube_id != NULL)
                            $video_youtube->setYoutubeId($video_youtube_id);
                    }
                    else {
                        $video_youtube = new VideoYoutube;
                        $video_youtube->setVideo($video);

                        if($video_youtube_id != NULL)
                            $video_youtube->setYoutubeId($video_youtube_id);
                    }

                    $em->persist($video_youtube);
                    $em->flush();
                }


                // If the project is changed, move the files on AWS
                if($project_id != NULL) {

                    // Move the screenshot and the files of the video
                    $video_action = $this->container->get('strime_api.helpers.video_action');
                    $video_action->aws_key = $this->container->getParameter('aws_key');
                    $video_action->aws_secret = $this->container->getParameter('aws_secret');
                    $video_action->aws_region = $this->container->getParameter('aws_region');
                    $video_action->aws_bucket = $this->container->getParameter('aws_bucket');
                    $video_action->old_project_id = $old_project_id;
                    $video_action->video = $video;
                    $video_action->moveVideoOnAmazon();
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
     * @Route("/video/{secret_id}/delete")
     * @Template()
     */
    public function deleteVideoAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/video/{video_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $video = new Video;

        // Get the video
        $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($video) || ($video == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_source"] = "video_doesnt_exist";
            $json["error_message"] = "This video ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

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

                // Then we delete the video itself
                $em->remove($video);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "204";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 204, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
            catch (Exception $e) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_deleting_video";
                $json["message"] = "An error occured while deleting data from the database.";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // Return the results
        return $response;
    }
}
