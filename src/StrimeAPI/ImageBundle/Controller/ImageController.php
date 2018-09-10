<?php

namespace StrimeAPI\ImageBundle\Controller;

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

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\VideoBundle\Entity\Project;
use StrimeAPI\ImageBundle\Entity\Image;
use StrimeAPI\ImageBundle\Entity\ImageComment;
use StrimeAPI\UserBundle\Entity\Contact;

class ImageController extends FOSRestController implements TokenAuthenticatedController
{
    /**
     * @Route("/images/get")
     * @Template()
     */
    public function getImagesAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/images/get"
        );


        // Set Doctrine Manager and get the objects
        $em = $this->getDoctrine()->getManager();

        // Get the images
        $images_results = $em->getRepository('StrimeAPIImageBundle:Image')->findBy(array(), array('id' => 'DESC'));
        $images = array();

        // If no offer has been created yet.
        if($images_results == NULL) {
            $images = "No image has been found.";
            $json["results"] = $images;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($images_results as $image) {

            // Get the project details
            $project = new Project;

            if($image->getProject() == NULL) {
                $project_details = NULL;
            }
            else {
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $image->getProject()));
                $project_details = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }

            // Set the data that will be returned in the results
            if($project == NULL) {
                $project = "This image doesn't belong to a project.";
            }

            // Get the number of assets in the project
            $nb_assets_in_project = 0;
            if($project_details != NULL) {

                $project_action = $this->container->get('strime_api.helpers.project_action');
                $project_action->project = $image->getProject();
                $nb_assets_in_project = $project_action->countAssetsInProject();
            }

            if($image->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this image
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIImageBundle:ImageComment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.image = :image')
                ->setParameter('image', $image->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Set the results
            $images[] = array(
                "asset_type" => "image",
                "asset_id" => $image->getSecretId(),
                "name" => $image->getName(),
                "description" => $image->getDescription(),
                "project" => $project_details,
                "user" => array(
                    "user_id" => $image->getUser()->getSecretId(),
                    "first_name" => $image->getUser()->getFirstName(),
                    "last_name" => $image->getUser()->getLastName()
                ),
                "file" => $image->getS3Url(),
                "thumbnail" => $image->getS3ThumbnailUrl(),
                "size" => $image->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "created_at" => $image->getCreatedAt(),
                "updated_at" => $image->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $images;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }

    /**
     * @Route("/images/project/{project_id}/get")
     * @Template()
     */
    public function getImagesByProjectAction(Request $request, $project_id)
    {
    	// Prepare the response
    	$json = array(
    		"application" => $this->container->getParameter('app_name'),
    		"version" => $this->container->getParameter('app_version'),
    		"method" => "/images/project/{project_id}/get"
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

        // Get the images
		$images_results = $em->getRepository('StrimeAPIImageBundle:Image')->findBy(array('project' => $project), array('id' => 'DESC'));
		$images = array();

		// If no offer has been created yet.
		if($images_results == NULL) {
			$images = "No image has been found for this project.";
			$json["results"] = $images;
        	return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        	exit;
		}

		// If we get a result
		// Prepare the array containing the results
		foreach ($images_results as $image) {

            // Get the user details
            $user = new User;
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $image->getUser()));

            // Set the data that will be returned in the results
            if($user == NULL) {
                $user = "The user associated to this image has been deleted.";
            }

            if($image->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this video, if it's not a project
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIImageBundle:ImageComment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.image = :image')
                ->setParameter('image', $image->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Set the results
			$images[] = array(
                "asset_type" => "image",
				"asset_id" => $image->getSecretId(),
				"name" => $image->getName(),
                "description" => $image->getDescription(),
				"project" => array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                ),
                "user" => array(
                    "secret_id" => $user->getSecretId()
                ),
                "file" => $image->getS3Url(),
                "thumbnail" => $image->getS3ThumbnailUrl(),
                "size" => $image->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
				"created_at" => $image->getCreatedAt(),
				"updated_at" => $image->getUpdatedAt()
			);
		}

        // Add the results to the response
        $json["results"] = $images;
    	$response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }

    /**
     * @Route("/images/user/{user_id}/get")
     * @Template()
     */
    public function getImagesByUserAction(Request $request, $user_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/images/user/{user_id}/get"
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

        // Get the images
        $images_results = $em->getRepository('StrimeAPIImageBundle:Image')->findBy(array('user' => $user), array('id' => 'DESC'));
        $images = array();

        // If no image has been created yet.
        if($images_results == NULL) {
            $images = "No image has been found for this user.";
            $json["results"] = $images;
            return new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
            exit;
        }

        // If we get a result
        // Prepare the array containing the results
        foreach ($images_results as $image) {

            // Get the project details
            $project = new Project;

            if($image->getProject() == NULL) {
                $project_details = NULL;
            }
            else {
                $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $image->getProject()));
                $project_details = array(
                    "project_id" => $project->getSecretId(),
                    "name" => $project->getName(),
                    "description" => $project->getDescription()
                );
            }

            // Set the data that will be returned in the results
            if($project == NULL) {
                $project = "This image doesn't belong to a project.";
            }

            if($image->getPassword() != NULL)
                $password = TRUE;
            else
                $password = FALSE;

            // Get the number of comments for this video
            $comments = $this->getDoctrine()
                ->getRepository('StrimeAPIImageBundle:ImageComment');

            $query = $comments->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.image = :image')
                ->setParameter('image', $image->getId())
                ->getQuery();

            $nb_comments = $query->getSingleScalarResult();

            // Get the number of images in the project
            $nb_assets_in_project = 0;
            if($project_details != NULL) {

                $project_action = $this->container->get('strime_api.helpers.project_action');
                $project_action->project = $image->getProject();
                $nb_assets_in_project = $project_action->countAssetsInProject();
            }

            // Set the results
            $images[] = array(
                "asset_type" => "image",
                "asset_id" => $image->getSecretId(),
                "name" => $image->getName(),
                "description" => $image->getDescription(),
                "project" => $project_details,
                "user" => array(
                    "secret_id" => $user->getSecretId()
                ),
                "file" => $image->getS3Url(),
                "thumbnail" => $image->getS3ThumbnailUrl(),
                "size" => $image->getSize(),
                "password" => $password,
                "nb_comments" => $nb_comments,
                "nb_assets_in_project" => $nb_assets_in_project,
                "created_at" => $image->getCreatedAt(),
                "updated_at" => $image->getUpdatedAt()
            );
        }

        // Add the results to the response
        $json["results"] = $images;
        $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));

        // Return the results
        return $response;
    }


    /**
     * @Route("/image/{secret_id}/get")
     * @Template()
     */
    public function getImageAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/{image_id}/get"
        );

        // Set Doctrine Manager and create the object
        $em = $this->getDoctrine()->getManager();
        $image_details = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $secret_id));
        $image = array();

        // If we get a result
        if($image_details != NULL) {

            // Get the details of the project to which belongs this video
            $project = $em->getRepository('StrimeAPIVideoBundle:Project')->findOneBy(array('id' => $image_details->getProject()));

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

            // Get the details of the user who owns the image
            $user = $em->getRepository('StrimeAPIUserBundle:User')->findOneBy(array('id' => $image_details->getUser()));

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
            $contacts = $image_details->getContacts();
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

            // Prepare the array containing the results
            $image = array(
                "asset_type" => "image",
                "asset_id" => $image_details->getSecretId(),
                "user" => $user_content,
                "project" => $project_content,
                "name" => $image_details->getName(),
                "description" => $image_details->getDescription(),
                "s3_url" => $image_details->getS3Url(),
                "thumbnail" => $image_details->getS3ThumbnailUrl(),
                "size" => $image_details->getSize(),
                "contacts" => $contacts_list,
                "created_at" => $image_details->getCreatedAt(),
                "updated_at" => $image_details->getUpdatedAt()
            );

            // Add the results to the response
            $json["results"] = $image;
            $response = new JsonResponse($json, 200, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If there is no user with this ID
        else  {
            $image = "No image has been found with this ID.";
            $json["results"] = $image;
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // Return the results
        return $response;
    }


    /**
     * @Route("/image/add")
     * @Template()
     */
    public function addImageAction(Request $request)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/add"
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
            $image = new Image;

            // We generate a secret_id
            $secret_id_exists = TRUE;
            $token_generator = new TokenGenerator();
            while($secret_id_exists != NULL) {
                $secret_id = $token_generator->generateToken(10);
                $secret_id_exists = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $secret_id));
            }

            // We create the image
            try {
                // Create the entity
                $image->setSecretId($secret_id);
                $image->setName($name);
                $image->setUser($user);
                $image->setProject($project);
                $image->setPassword($password);
                $image->setFile($file);

                if($description != NULL) {
                    $image->setDescription($description);
                }

                $em->persist($image);
                $em->flush();

                // Set the full path of the image
                $full_image_path = realpath( __DIR__ . '/../../../../web/' . $image->getImage() );

                // Check if the size of the image doesn't exceed the amount of space remaining for this user
                $filesize = filesize( $image->getImage() );
                $total_amount_space_used_by_user = $user->getStorageUsed() + $filesize;
                $storage_multiplier = $this->container->getParameter('storage_multiplier');

                if($total_amount_space_used_by_user > ($user->getOffer()->getStorageAllowed() * $storage_multiplier)) {

                    // We delete the image
                    unlink( $full_image_path );
                    $em->remove($image);
                    $em->flush();

                    $json["status"] = "error";
                    $json["response_code"] = "400";
                    $json["error_message"] = "You are exceeding the amount of space allocated with your current plan. Please upgrade to upload this image.";
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

                    // Get the size of the image
                    list($image_width, $image_height) = getimagesize($full_image_path);

                    // Update the DB with the size of the file
                    $image->setSize($filesize);
                    $image->setWidth($image_width);
                    $image->setHeight($image_height);
                    $em->persist($image);
                    $em->flush();
                }

                // Create a thumbnail
                /** @var ImagineController */
                $imagine = $this->container->get('liip_imagine.controller');

                /** @var RedirectResponse */
                $image_manager_response = $imagine->filterAction(
                    $request,                  // http request
                    $image->getImage(),        // original image you want to apply a filter to
                    'image_dashboard_thumb'    // filter defined in config.yml
                );

                /** @var CacheManager */
                $cache_manager = $this->container->get('liip_imagine.cache.manager');

                /** @var string */
                $thumbnail_source_path = $cache_manager->getBrowserPath($image->getImage(), 'image_dashboard_thumb');
                touch($thumbnail_source_path);

                // Prepare the thumbnail_source_path to get an absolute URL
                $thumbnail_source_absolute_path = realpath( __DIR__ . '/../../../../web/media/cache/image_dashboard_thumb/' . $image->getImage() );

                // Prepare the name of the file for Amazon
                $thumbnail_basename = basename( $thumbnail_source_path );
                $thumbnail_extension = pathinfo($thumbnail_basename, PATHINFO_EXTENSION);
                $thumbnail_basename = substr($thumbnail_basename, 0, -(strlen($thumbnail_extension) + 1)) . "-thumb." . $thumbnail_extension;


                // Send the files to Amazon
                // Instantiate the S3 client using your credential profile
                $aws = S3Client::factory(array(
                    'credentials' => array(
                        'key'       => $this->container->getParameter('aws_key'),
                        'secret'    => $this->container->getParameter('aws_secret')
                    ),
                    'version' => 'latest',
                    'region' => $this->container->getParameter('aws_region')
                ));

                // Get the buckets list
                $buckets_list = $aws->listBuckets();

                // Generate the bucket folder
                $bucket_folder = $user_id."/";
                if($project_id != NULL)
                    $bucket_folder .= $project_id."/";

                // Set the upload path
                $upload_path = realpath( __DIR__.'/../../../../web/uploads/images/'.$user->getSecretId() ) . '/';

                // Create the base folder if it doesn't exist
                if( !file_exists( $upload_path ) )
                    mkdir( $upload_path, 0755, TRUE );


                // Set the upload variables
                $s3_upload = $s3_upload_thumb = NULL;


                // Send the files to Amazon S3
                foreach ($buckets_list['Buckets'] as $bucket) {

                    if(strcmp($bucket['Name'], $this->container->getParameter('aws_bucket_images')) == 0) {

                        // Upload the image
                        $s3_upload = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder . basename( $image->getImage() ),
                            'SourceFile' => $full_image_path
                        ));

                        // Upload the thumbnail
                        $s3_upload_thumb = $aws->putObject(array(
                            'Bucket'     => $bucket['Name'],
                            'Key'        => $bucket_folder . $thumbnail_basename,
                            'SourceFile' => $thumbnail_source_absolute_path
                        ));

                    }
                }

                // If the file has been uploaded to Amazon
                if($s3_upload != NULL) {

                    // Get the URL of the file on Amazon S3
                    $s3_https_url = $s3_upload['ObjectURL'];

                    // Delete the files locally
                    unlink($full_image_path);

                    // Update the image with Amazon S3 URLs
                    $image->setS3Url($s3_https_url);

                    // If the thumbnail has been uploaded to Amazon
                    if($s3_upload_thumb != NULL) {

                        // Get the URL of the file on Amazon S3
                        $s3_https_url = $s3_upload_thumb['ObjectURL'];

                        // Update the image with Amazon S3 URLs
                        $image->setS3ThumbnailUrl($s3_https_url);
                    }

                    // Save the changes
                    $em->persist($image);
                    $em->flush();
                }

                // If the file has not been uploaded to Amazon
                // Remove the image and return an error
                else {
                    $em->remove($image);
                    $em->flush();

                    // Delete the files locally
                    unlink($full_image_path);

                    $json["status"] = "error";
                    $json["response_code"] = "400";
                    $json["error_message"] = "The image has not been uploaded to Amazon.";
                    $json["error_source"] = "image_not_uploaded_to_amazon";

                    // Create the response object and initialize it
                    return new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
                    exit;
                }

                // Prepare the response
                $json["status"] = "success";
                $json["response_code"] = "201";
                $json["image"] = array(
                    "asset_type" => "image",
                    "asset_id" => $image->getSecretId(),
                    "name" => $image->getName(),
                    "thumbnail" => $image->getS3ThumbnailUrl(),
                    "created_at" => $image->getCreatedAt()->getTimestamp()
                );
                if($image->getProject() != NULL) {
                    $json["project"] = array(
                        "project_id" => $image->getProject()->getSecretId(),
                        "name" => $image->getProject()->getName(),
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
     * @Route("/image/{secret_id}/edit")
     * @Template()
     */
    public function editImageAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/{image_id}/edit"
        );

        // Get the data
        $name = $request->request->get('name', NULL);
        $description = $request->request->get('description', NULL);
        $empty_description = $request->request->get('empty_description', 0);
        $project_id = $request->request->get('project_id', NULL);
        $password = $request->request->get('password', NULL);
        $contacts = $request->request->get('contacts', NULL);

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $image = new Image;

        // Get the image
        $image = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($image) || ($image == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["message"] = "This image ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // We edit the image
            try {
                if($name != NULL)
                    $image->setName($name);

                if(($description != NULL) && ($empty_description == 0)) {
                    $image->setDescription($description);
                }
                elseif($empty_description == 1) {
                    $image->setDescription(NULL);
                }

                if($project_id != NULL) {

                    // Keep the old project in a variable for AWS
                    if($image->getProject() != NULL)
                        $old_project_id = $image->getProject()->getSecretId();
                    else
                        $old_project_id = NULL;

                    // Set the new project
                    $image->setProject($project);
                }

                if($password != NULL)
                    $image->setPassword($password);

                // Edit the contacts if needed
                $current_contacts = $image->getContacts();

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
                                $image->addContact($contact);
                            }
                        }
                    }
                }

                $em->persist($image);
                $em->flush();


                // If the project is changed, move the files on AWS
                if($project_id != NULL) {

                    // Move the screenshot and the files of the video
                    $image_action = $this->container->get('strime_api.helpers.image_action');
                    $image_action->aws_key = $this->container->getParameter('aws_key');
                    $image_action->aws_secret = $this->container->getParameter('aws_secret');
                    $image_action->aws_region = $this->container->getParameter('aws_region');
                    $image_action->aws_bucket = $this->container->getParameter('aws_bucket_images');
                    $image_action->old_project_id = $old_project_id;
                    $image_action->image = $image;
                    $image_action->moveImageOnAmazon();
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
     * @Route("/image/{secret_id}/delete")
     * @Template()
     */
    public function deleteImageAction(Request $request, $secret_id)
    {
        // Prepare the response
        $json = array(
            "application" => $this->container->getParameter('app_name'),
            "version" => $this->container->getParameter('app_version'),
            "method" => "/image/{image_id}/delete"
        );

        // Prepare the entity
        $em = $this->getDoctrine()->getManager();
        $image = new Image;

        // Get the image
        $image = $em->getRepository('StrimeAPIImageBundle:Image')->findOneBy(array('secret_id' => $secret_id));

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
        elseif(!is_object($image) || ($image == NULL)) {

            // Set the content of the response
            $json["status"] = "error";
            $json["response_code"] = "400";
            $json["error_source"] = "image_doesnt_exist";
            $json["error_message"] = "This image ID doesn't exist.";

            // Create the response object and initialize it
            $response = new JsonResponse($json, 400, array('Access-Control-Allow-Origin' => TRUE, 'Content-Type' => 'application/json'));
        }

        // If everything is fine in this request
        else {

            // Set the object
            $image_action = $this->container->get('strime_api.helpers.image_action');
            $image_action->aws_key = $this->container->getParameter('aws_key');
            $image_action->aws_secret = $this->container->getParameter('aws_secret');
            $image_action->aws_region = $this->container->getParameter('aws_region');
            $image_action->aws_bucket = $this->container->getParameter('aws_bucket_images');
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

                // Then we delete the image itself
                $em->remove($image);
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
