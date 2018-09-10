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
use StrimeAPI\VideoBundle\Helpers\VideoAction;

use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\VideoBundle\Entity\EncodingJob;

class ProjectController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/projects/{user_id}/get")
     * @Template()
     */
    public function getProjectsByUserAction(Request $request, $user_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/projects/{user_id}/get"
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

        // Get the projects
		$projects_results = $em->getRepository('StrimeAPIVideoBundle:Project')->findByUser($user);
		$projects = array();

		// If no project has been created yet.
		if($projects_results == NULL) {
			$projects = "No project has been found for this user.";
			$json["results"] = $projects;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($projects_results as $project) {

            // Count the number of videos per project
            $query = $em->createQueryBuilder();
            $query->select( 'count(api_video.id)' );
            $query->from( 'StrimeAPIVideoBundle:Video', 'api_video' );
            $query->where( 'api_video.project = :project' );
            $query->setParameter('project', $project);
            $nb_videos = $query->getQuery()->getSingleScalarResult();

            // Count the number of images per project
            $query = $em->createQueryBuilder();
            $query->select( 'count(api_image.id)' );
            $query->from( 'StrimeAPIImageBundle:Image', 'api_image' );
            $query->where( 'api_image.project = :project' );
            $query->setParameter('project', $project);
            $nb_images = $query->getQuery()->getSingleScalarResult();

            // Set the total number of assets
            $nb_assets = $nb_videos + $nb_images;

			$projects[] = array(
				"project_id" => $project->getSecretId(),
				"name" => $project->getName(),
				"description" => $project->getDescription(),
                "nb_assets" => $nb_assets,
				"user" => array(
					"user_id" => $user->getSecretId(),
					"first_name" => $user->getFirstName(),
					"last_name" => $user->getLastName(),
					"email" => $user->getEmail()
				),
				"created_at" => $project->getCreatedAt(),
				"updated_at" => $project->getUpdatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $projects;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/project/{secret_id}/get")
     * @Template()
     */
    public function getProjectAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/project/{project_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $project_details = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $secret_id));
        $project = array();

        // If we get a result
        if($project_details != NULL) {

            // Get the details of the user who owns the project
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $project_details->getUser()));

            // Prepare the user content
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

            // Get the last video of the project to use its screenshot
            $query = $em->createQueryBuilder();
            $query->select( 'api_video' );
            $query->from( 'StrimeAPIVideoBundle:Video','api_video' );
            $query->where('api_video.project  = :project');
            $query->orderBy('api_video.id', 'DESC');
            $query->setMaxResults(1);
            $query->setParameter( 'project', $project_details );
            $last_video = $query->getQuery()->getOneOrNullResult();

            // Get the last image of the project to use its screenshot
            $query = $em->createQueryBuilder();
            $query->select( 'api_image' );
            $query->from( 'StrimeAPIImageBundle:Image','api_image' );
            $query->where('api_image.project  = :project');
            $query->orderBy('api_image.id', 'DESC');
            $query->setMaxResults(1);
            $query->setParameter( 'project', $project_details );
            $last_image = $query->getQuery()->getOneOrNullResult();

            // Get the last audio of the project to use its screenshot
            $query = $em->createQueryBuilder();
            $query->select( 'api_audio' );
            $query->from( 'StrimeAPIAudioBundle:Audio','api_audio' );
            $query->where('api_audio.project  = :project');
            $query->orderBy('api_audio.id', 'DESC');
            $query->setMaxResults(1);
            $query->setParameter( 'project', $project_details );
            $last_audio = $query->getQuery()->getOneOrNullResult();

            // Save these elements in an array
            $assets_list = array();
            if($last_video != NULL) {
                $assets_list["video"] = $last_video;
            }
            if($last_image != NULL) {
                $assets_list["image"] = $last_image;
            }
            if($last_audio != NULL) {
                $assets_list["audio"] = $last_audio;
            }

            // Order this array by creation_date
            usort($assets_list, function($a, $b) {
                return strcmp($b->getCreatedAt()->format('U'), $a->getCreatedAt()->format('U'));
            });

            // Set the screenshot
            $project_screenshot = NULL;

            if(count($assets_list) > 0) {
                foreach ($assets_list as $key => $value) {
                    if($project_screenshot == NULL) {

                        if ($assets_list[$key] instanceof \StrimeAPI\VideoBundle\Entity\Video)
                            $project_screenshot_asset_type = "video";
                        elseif ($assets_list[$key] instanceof \StrimeAPI\ImageBundle\Entity\Image)
                            $project_screenshot_asset_type = "image";
                        elseif ($assets_list[$key] instanceof \StrimeAPI\AudioBundle\Entity\Audio)
                            $project_screenshot_asset_type = "audio";

                        if ($assets_list[$key] instanceof \StrimeAPI\VideoBundle\Entity\Video)
                            $project_screenshot = $assets_list[$key]->getS3ScreenshotUrl();
                        else
                            $project_screenshot = $assets_list[$key]->getS3ThumbnailUrl();
                    }
                }
            }

            // Prepare the array containing the results
            $project = array(
                "project_id" => $project_details->getSecretId(),
                "user" => $user_content,
                "name" => $project_details->getName(),
                "description" => $project_details->getDescription(),
                "contacts" => $project_details->getContacts(),
                "screenshot" => $project_screenshot,
                "screenshot_asset_type" => $project_screenshot_asset_type,
                "created_at" => $project_details->getCreatedAt(),
                "updated_at" => $project_details->getUpdatedAt()
            );

            // Add the results to the response
            $json["results"] = $project;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $project = "No project has been found with this ID.";
            $json["results"] = $project;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/project/add")
     * @Template()
     */
    public function addProjectAction(Request $request)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/project/add"
    	);

    	// Get the data
    	$name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);
    	$user_id = $request->request->get('user_id', NULL);

    	// Get the user details
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));

        // If no user has been found with this user ID
        if($user == NULL) {
        	$json['authorization'] = "No user has been found with this ID.";
            $json["error_source"] = "no_user_found";
            return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // Check if this project already exists
        $project_exists = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('user' => $user, 'name' => $name));

        // If the project already exists
        if($project_exists != NULL) {
        	$json['authorization'] = "This project already exists.";
            $json["error_source"] = "project_name_already_used";
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
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
        	$response = new JsonResponse($json, 405, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If some data are missing
        elseif(($name == NULL) || ($user_id == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "Some data are missing.";
            $json["error_source"] = "missing_data";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Prepare the entity
            $project = new Project;

            // We generate a secret_id
            $secret_id_exists = TRUE;
		    $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
		        $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $secret_id));
            }

            // We create the project
            try {
                $project->setSecretId($secret_id);
                $project->setName($name);
                $project->setDescription($description);
                $project->setUser($user);

                $em->persist($project);
                $em->flush();

                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["project_id"] = $project->getSecretId();

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
     * @Route("/project/{secret_id}/edit")
     * @Template()
     */
    public function editProjectAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/project/{project_id}/edit"
        );

        // Get the data
        $name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);
        $contacts = $request->request->get('contacts', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $project = new Project;

        // Get the project
        $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($project) || ($project == NULL)) {

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
                if($name != NULL)
                    $project->setName($name);

                if($description != NULL)
                    $project->setDescription($description);

                // Edit the contacts if needed
                $current_contacts = $project->getContacts();

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
                                $project->addContact($contact);
                            }
                        }
                    }
                }

                $em->persist($project);
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
     * @Route("/project/{secret_id}/delete")
     * @Template()
     */
    public function deleteProjectAction(Request $request, $secret_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/project/{project_id}/delete"
    	);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $project = new Project;

    	// Get the project
        $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($project) || ($project == NULL)) {

        	// Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_source"] = "project_doesnt_exist";
            $json["error_message"] = "This project ID doesn't exist.";

        	// Create the response object and initialize it
        	$response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We get all the encoding jobs associated to the project
            $encoding_jobs = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findBy(array('project' => $project));

            // Foreach encoding job, delete it
            if(is_array($encoding_jobs)) {
                foreach ($encoding_jobs as $encoding_job) {
                    $em->remove($encoding_job);
                    $em->flush();
                }
            }

            // We get all the video associated to this project
            $videos = $em->getRepository('StrimeAPIVideoBundle:Video')->findBy(array('project' => $project));

            // Delete all the videos included in the project
            if(is_array($videos)) {

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

            // We get all the images associated to this project
            $images = $em->getRepository('StrimeAPIImageBundle:Image')->findBy(array('project' => $project));

            // Delete all the images included in the project
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

            // We get all the audio files associated to this project
            $audios = $em->getRepository('StrimeAPIAudioBundle:Audio')->findBy(array('project' => $project));

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
                        $em->remove($audio);
                        $em->flush();
                    }
                    catch (Exception $e) {
                        $flag_audios_deletion = FALSE;
                    }
                }
            }

            // If an error occured while deleting the videos of the project
            if(!$flag_videos_deletion) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_deleting_video";
                $json["message"] = "An error occured while deleting the videos associated to the project.";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // If an error occured while deleting the images of the project
            elseif(!$flag_images_deletion) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_deleting_image";
                $json["message"] = "An error occured while deleting the images associated to the project.";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // If an error occured while deleting the audio files of the project
            elseif(!$flag_audios_deletion) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_deleting_audio";
                $json["message"] = "An error occured while deleting the audios associated to the project.";

                // Create the response object and initialize it
                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // If all the videos of the project have been properly deleted
            else {

                // We delete the project
                try {

                    // First, we delete all the associated contacts
                    $contacts = $project->getContacts();

                    if(($contacts != NULL) && (is_array($contacts))) {
                        foreach ($contacts as $contact) {
                            $project->removeContact($contact);
                        }
                    }

                    // Then we delete the project itself
                    $em->remove($project);
                    $em->flush();

                    $json["status"] = "success";
                    $json["response_code"] = "204";

                    // Create the response object and initialize it
                    $response = new JsonResponse($json, 204, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }
                catch (Exception $e) {
                    $json["status"] = "error";
                    $json["response_code"] = "520";
                    $json["error_source"] = "error_deleting_project";
                    $json["message"] = "An error occured while deleting data from the database.";

                    // Create the response object and initialize it
                    $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                }
            }
        }

        // Return the results
        return $response;
    }
}
