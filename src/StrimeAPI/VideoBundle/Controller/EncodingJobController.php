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

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\VideoBundle\Entity\EncodingJob;
use StrimeAPI\StatsBundle\Entity\EncodingJobTime;

class EncodingJobController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/encoding-jobs/video/get")
     * @Template()
     */
    public function getEncodingJobsAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-jobs/video/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the encoding jobs
        $encoding_jobs_results = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findAll();

        // If no encoding job has been created yet.
        if($encoding_jobs_results == NULL) {
            $encoding_jobs = "No encoding job has been found.";
            $json["results"] = $encoding_jobs;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        $encoding_jobs = array();

        foreach ($encoding_jobs_results as $encoding_job) {

            $user_details = array(
                "user_id" => $encoding_job->getUser()->getSecretId(),
                "first_name" => $encoding_job->getUser()->getFirstName(),
                "last_name" => $encoding_job->getUser()->getLastName(),
                "email" => $encoding_job->getUser()->getEmail()
            );

            // Get the project of the encoding video
            if( $encoding_job->getVideo()->getProject() == NULL )
                $project = NULL;
            else
                $project = $encoding_job->getVideo()->getProject()->getName();


            $encoding_jobs[] = array(
                "encoding_job_id" => $encoding_job->getSecretId(),
                "status" => $encoding_job->getStatus(),
                "error_code" => $encoding_job->getErrorCode(),
                "started" => $encoding_job->getStarted(),
                "filename" => $encoding_job->getFilename(),
                "upload_path" => $encoding_job->getUploadPath(),
                "full_video_path" => $encoding_job->getFullVideoPath(),
                "encoding_server" => $encoding_job->getEncodingServer(),
                "user" => $user_details,
                "video" => array(
                    "video_id" => $encoding_job->getVideo()->getSecretId(),
                    "name" => $encoding_job->getVideo()->getName(),
                    "project" => $project,
                    "screenshot" => $encoding_job->getVideo()->getS3ScreenshotUrl(),
                ),
                "created_at" => $encoding_job->getCreatedAt(),
                "updated_at" => $encoding_job->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $encoding_jobs;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-jobs/video/get/by/{data_type}/{data_id}")
     * @Template()
     */
    public function getEncodingJobsByUserAction(Request $request, $data_type, $data_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/encoding-jobs/video/get/by/{data_type}/{data_id}"
    	);


    	// Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the user details
        if(strcmp($data_type, "user") == 0) {
            $user = new User;
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $data_id));

            if($user == NULL) {
                $json['authorization'] = "No user has been found with this ID.";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                exit;
            }
        }
        elseif(strcmp($data_type, "project") == 0) {
            $project = new Project;
            $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $data_id));

            if($project == NULL) {
                $json['authorization'] = "No project has been found with this ID.";
                return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                exit;
            }
        }

        // Get the encoding jobs
        if(strcmp($data_type, "user") == 0) {
            $encoding_jobs_results = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findByUser($user);
        }
        elseif(strcmp($data_type, "project") == 0) {
            $encoding_jobs_results = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findByProject($project);
        }

		// If no project has been created yet.
		if($encoding_jobs_results == NULL) {
			$encoding_jobs = "No encoding job has been found for this ".$data_type.".";
			$json["results"] = $encoding_jobs;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
        $encoding_jobs = array();

		foreach ($encoding_jobs_results as $encoding_job) {

            if(strcmp($data_type, "user") == 0) {
                $user_details = array(
                    "user_id" => $user->getSecretId(),
                    "first_name" => $user->getFirstName(),
                    "last_name" => $user->getLastName(),
                    "email" => $user->getEmail()
                );
            }
            elseif(strcmp($data_type, "project") == 0) {
                $user_details = NULL;
            }

            // Get the project of the encoding video
            if( $encoding_job->getVideo()->getProject() == NULL )
                $project = NULL;
            else
                $project = $encoding_job->getVideo()->getProject()->getName();


			$encoding_jobs[] = array(
                "encoding_job_id" => $encoding_job->getSecretId(),
				"status" => $encoding_job->getStatus(),
                "error_code" => $encoding_job->getErrorCode(),
                "started" => $encoding_job->getStarted(),
                "filename" => $encoding_job->getFilename(),
                "upload_path" => $encoding_job->getUploadPath(),
                "full_video_path" => $encoding_job->getFullVideoPath(),
                "encoding_server" => $encoding_job->getEncodingServer(),
				"user" => $user_details,
                "video" => array(
                    "video_id" => $encoding_job->getVideo()->getSecretId(),
                    "name" => $encoding_job->getVideo()->getName(),
                    "project" => $project,
                    "screenshot" => $encoding_job->getVideo()->getS3ScreenshotUrl(),
                ),
				"created_at" => $encoding_job->getCreatedAt(),
				"updated_at" => $encoding_job->getUpdatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $encoding_jobs;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-job/video/{secret_id}/get")
     * @Template()
     */
    public function getEncodingJobAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/{encoding_job_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $encoding_job_details = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $secret_id));
        $encoding_job = array();

        // If we get a result
        if($encoding_job_details != NULL) {

            // Get the details of the user who owns the encoding job
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $encoding_job_details->getUser()));
            $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('id' => $encoding_job_details->getVideo()));

            if( $encoding_job_details->getProject() != NULL )
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $encoding_job_details->getProject()));
            else
                $project = NULL;

            // Prepare the array containing the results
            $encoding_job = array(
                "encoding_job_id" => $encoding_job_details->getSecretId(),
                "status" => $encoding_job_details->getStatus(),
                "started" => $encoding_job_details->getStarted(),
                "error_code" => $encoding_job_details->getErrorCode(),
                "filename" => $encoding_job_details->getFilename(),
                "upload_path" => $encoding_job_details->getUploadPath(),
                "full_video_path" => $encoding_job_details->getFullVideoPath(),
                "encoding_server" => $encoding_job_details->getEncodingServer(),
                "created_at" => $encoding_job_details->getCreatedAt(),
                "updated_at" => $encoding_job_details->getUpdatedAt(),
            );

            if($user != NULL)
                $encoding_job["user_id"] = $user->getSecretId();
            else
                $encoding_job["user_id"] = NULL;

            if($video != NULL)
                $encoding_job["video_id"] = $video->getSecretId();
            else
                $encoding_job["video_id"] = NULL;

            if($project != NULL)
                $encoding_job["project_id"] = $project->getSecretId();
            else
                $encoding_job["project_id"] = NULL;

            // Add the results to the response
            $json["results"] = $encoding_job;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $comment = "No encoding job has been found with this ID.";
            $json["results"] = $encoding_job;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-job/video/{secret_id}/edit")
     * @Template()
     */
    public function editEncodingJobAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/{encoding_job_id}/edit"
        );

        $user_id = $request->request->get('user_id', NULL);
        $video_id = $request->request->get('video_id', NULL);
        $project_id = $request->request->get('project_id', NULL);
        $status = $request->request->get('status', NULL);
        $error_code = $request->request->get('error_code', NULL);
        $started = $request->request->get('started', NULL);
        $filename = $request->request->get('filename', NULL);
        $upload_path = $request->request->get('upload_path', NULL);
        $full_video_path = $request->request->get('full_video_path', NULL);


        // Get the encoding job details
        $em = $this->getDoctrine()->getManager();
        $encoding_job = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($encoding_job) || ($encoding_job == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This encoding job ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the project
            try {
                if($user_id != NULL) {
                    $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('secret_id' => $user_id));
                    if($user != NULL)
                        $encoding_job->setUser($user);
                }

                if($video_id != NULL) {
                    $video = $em->getRepository('StrimeAPIVideoBundle:Video')->findOneBy(array('secret_id' => $video_id));
                    if($video != NULL)
                        $encoding_job->setVideo($video);
                }

                if($project_id != NULL) {
                    $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('secret_id' => $project_id));
                    if($project != NULL)
                        $encoding_job->setProject($project);
                }

                if(is_int($status))
                    $encoding_job->setStatus($status);

                if($error_code != NULL)
                    $encoding_job->setErrorCode($error_code);

                if(is_int($started))
                    $encoding_job->setStarted($started);

                if($filename != NULL)
                    $encoding_job->setFilename($filename);

                if($upload_path != NULL)
                    $encoding_job->setUploadPath($upload_path);

                if($full_video_path != NULL)
                    $encoding_job->setFullVideoPath($full_video_path);

                $em->persist($encoding_job);
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
     * @Route("/encoding-job/video/{secret_id}/delete")
     * @Template()
     */
    public function deleteEncodingJobAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/{project_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $encoding_job = new EncodingJob;

        // Get the comment
        $encoding_job = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($encoding_job) || ($encoding_job == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "This encoding job ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Clean the EncodingJobTime table
            if($encoding_job->getVideo() != NULL) {
                $encoding_job_time = $em->getRepository('StrimeAPIStatsBundle:EncodingJobTime')->findOneBy(array('video' => $encoding_job->getVideo()));

                if($encoding_job_time != NULL) {
                    $encoding_job_time->setVideo(NULL);
                    $em->persist($encoding_job_time);
                    $em->flush();
                }
            }

            // We delete the encoding job
            try {
                $em->remove($encoding_job);
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
     * @Route("/encoding-job/video/{secret_id}/delete-with-video")
     * @Template()
     */
    public function deleteEncodingJobAndAssociatedVideoAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/{project_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $encoding_job = new EncodingJob;

        // Get the comment
        $encoding_job = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($encoding_job) || ($encoding_job == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_message"] = "This encoding job ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Delete the associated video
            $flag_video_deletion = TRUE;

            // Apply the same process than in the video controller
            $video = $encoding_job->getVideo();

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

            // We delete the associated encoding jobs
            $video_action->deleteAssociatedEncodingJobs();

            // We delete all the associated contacts
            $video_action->deleteAssociatedContacts();

            // Clean the EncodingJobTime table
            $video_action = $this->container->get('strime_api.helpers.video_action');
            $video_action->video = $video;
            $video_action->deleteAssociatedEncodingJobsTimes();

            // We delete the video
            // Get the webhook parameters
            $strime_api_url = $this->container->getParameter('strime_api_url');
            $strime_api_token = $this->container->getParameter('strime_api_token');

            // Set the headers
            $headers_app = array(
                'Accept' => 'application/json',
                'X-Auth-Token' => $strime_api_token,
                'Content-type' => 'application/json'
            );

            // Set the endpoint
            $endpoint = $strime_api_url."video/".$video->getSecretId()."/delete";

            // Send a request on the corresponding webhook
            // Set Guzzle
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('DELETE', $endpoint, [
                'headers' => $headers_app,
                'http_errors' => false
            ]);

            $curl_status = $json_response->getStatusCode();
            $response = json_decode($json_response->getBody());

            // If an error occured while deleting the video been properly deleted
            if($curl_status != 204) {
                $json["status"] = "error";
                $json["response_code"] = "520";
                $json["error_source"] = "error_deleting_video";
                $json["message"] = "An error occured while deleting the video from the database.";

                $response = new JsonResponse($json, 520, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // Else, if the video has been properly deleted
            else {

                // We delete the encoding job
                try {
                    $em->remove($encoding_job);
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
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-job/video/new-worker/start")
     * @Template()
     */
    public function startNewJobAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/new-worker/start"
        );

        $encoding_server = $request->request->get('encoding_server', NULL);

        // Get the encoding jobs
        $em = $this->getDoctrine()->getManager();
        $encoding_jobs = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findBy(array('encoding_server' => $encoding_server), array('id' => 'ASC'));

        // If there is no result for this ID.
        if(!is_array($encoding_jobs) || ($encoding_jobs == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "No job has been found.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Set the variables
            $number_running_jobs = 0;
            $new_job = NULL;
            $start_job = FALSE;
            $max_running_jobs = $this->container->getParameter('strime_encoding_api_max_running_jobs');

            // Count the number of running jobs
            foreach ($encoding_jobs as $encoding_job) {

                if($encoding_job->getStarted() == 1)
                    $number_running_jobs++;

                elseif($new_job == NULL) {
                    $new_job = array(
                        "encoding_job_id" => $encoding_job->getSecretId(),
                        "status" => $encoding_job->getStatus(),
                        "error_code" => $encoding_job->getErrorCode(),
                        "started" => $encoding_job->getStarted(),
                        "filename" => $encoding_job->getFilename(),
                        "upload_path" => $encoding_job->getUploadPath(),
                        "full_video_path" => $encoding_job->getFullVideoPath(),
                        "encoding_server" => $encoding_job->getEncodingServer(),
                        "user_id" => $encoding_job->getUser()->getSecretId(),
                        "video_id" => $encoding_job->getVideo()->getSecretId(),
                        "created_at" => $encoding_job->getCreatedAt(),
                        "updated_at" => $encoding_job->getUpdatedAt()
                    );

                    if($encoding_job->getProject() != NULL)
                        $new_job["project_id"] = $encoding_job->getProject()->getSecretId();
                    else
                        $new_job["project_id"] = NULL;
                }
            }

            // If we have less than the max allowed number of running jobs
            if($number_running_jobs < $max_running_jobs)
                $start_job = TRUE;

            $json["status"] = "success";
            $json["response_code"] = "200";
            $json["start_job"] = $start_job;
            $json["new_job"] = $new_job;
            $json["number_running_jobs"] = $number_running_jobs;

            // Create the response object and initialize it
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-job/video/is-worker/stucked")
     * @Template()
     */
    public function isWorkerStuckedAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/is-worker/stucked"
        );

        // Get the encoding jobs
        $em = $this->getDoctrine()->getManager();
        $encoding_jobs = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findBy(array("started" => 1));

        // If there is no result for this ID.
        if(!is_array($encoding_jobs) || ($encoding_jobs == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "No job has been found.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Set the variables
            $stucked_jobs = array();

            // Check is the jobs have been stucked for a long time.
            foreach ($encoding_jobs as $encoding_job) {

                // Set the time of the last update + 20 min
                $last_update_plus_20_min = (int)$encoding_job->getUpdatedAt()->format('U') + (60 * 20);

                // If the actual time is bigger than the last update + 20 min
                if($last_update_plus_20_min < time())
                    $stucked_jobs[] = $encoding_job->getSecretId();
            }

            $json["status"] = "success";
            $json["response_code"] = "200";
            $json["stucked_jobs"] = $stucked_jobs;

            // Create the response object and initialize it
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-job/video/{secret_id}/update-stats/start-time")
     * @Template()
     */
    public function updateEncodingJobStartTimeInStatsAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/{encoding_job_id}/update-stats/start-time"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $encoding_job_details = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $secret_id));
        $encoding_job = array();

        // If we get a result
        if($encoding_job_details != NULL) {

            // Set the encoding_job_time object
            $encoding_job_time = new EncodingJobTime;

            // Create the entry in the table to measure the time of the encoding
            if($encoding_job_details->getVideo()->getSize() === NULL)
                $video_size = 0;
            else
                $video_size = $encoding_job_details->getVideo()->getSize();

            $encoding_job_time->setVideo( $encoding_job_details->getVideo() );
            $encoding_job_time->setSize( $video_size );
            $encoding_job_time->setDuration( $encoding_job_details->getVideo()->getDuration() );
            $encoding_job_time->setStartTime( time() );
            $em->persist($encoding_job_time);
            $em->flush();

            // Add the results to the response
            $json["results"] = $encoding_job;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $json["results"] = "No encoding job has been found with this ID.";
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/encoding-job/video/{secret_id}/update-stats/end-time")
     * @Template()
     */
    public function updateEncodingJobEndTimeInStatsAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/encoding-job/video/{encoding_job_id}/update-stats/end-time"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $encoding_job_details = $em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findOneBy(array('secret_id' => $secret_id));
        $encoding_job = array();

        // If we get a result
        if($encoding_job_details != NULL) {

            // Get the entity to update
            $encoding_job_time = $em->getRepository('StrimeAPIStatsBundle:EncodingJobTime')->findOneBy(array('video' => $encoding_job_details->getVideo()));

            // If we were not able to find the entity to update, we return an error
            if($encoding_job_time == NULL) {
                $json["results"] = "No stat raw has been found for this encoding job.";
                $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }

            // If we found the entity to update
            else {
                // Calculate the total time
                $end_time = time();
                $total_time = $end_time - $encoding_job_time->getStartTime();

                // Create the entry in the table to measure the time of the encoding
                $encoding_job_time->setEndTime( $end_time );
                $encoding_job_time->setTotalTime( $total_time );

                if(($end_time != 0) && ($end_time != NULL)) {
                    $encoding_job_time->setVideo( NULL );
                }

                $em->persist($encoding_job_time);
                $em->flush();

                // Add the results to the response
                $json["results"] = $encoding_job;
                $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            }
        }

        // If there is no user with this ID
        else  {
            $json["results"] = "No encoding job has been found with this ID.";
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }
}
