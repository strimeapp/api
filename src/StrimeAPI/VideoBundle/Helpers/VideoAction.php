<?php

namespace StrimeAPI\VideoBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

use StrimeAPI\VideoBundle\Entity\Video;
use StrimeAPI\VideoBundle\Entity\VideoYoutube;
use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\VideoBundle\Entity\CommentWithThumbInError;
use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\VideoBundle\Entity\EncodingJob;
use StrimeAPI\StatsBundle\Entity\EncodingJobTime;
use Aws\S3\S3Client;

class VideoAction {

    public $aws_key;
    public $aws_secret;
    public $aws_region;
    public $aws_bucket;
    public $video;
    public $old_project_id;
    public $comment;
    private $container;
    protected $em;



    public function __construct(EntityManager $em, Container $container) {
        $this->em = $em;
        $this->container = $container;
    }



    /**
     * @return null
     */
    public function deleteVideoFromAmazon()
    {
        // Instantiate the S3 client using your credential profile
        $aws = S3Client::factory(array(
            'credentials' => array(
                'key'       => $this->aws_key,
                'secret'    => $this->aws_secret
            ),
            'version' => 'latest',
            'region' => $this->aws_region
        ));

        // Get client instances from the service locator by name
        // $s3Client = $aws->get('s3');

        // Get the buckets list
        $buckets_list = $aws->listBuckets();


        // Delete the file from Amazon S3
        foreach ($buckets_list['Buckets'] as $bucket) {

            if(strcmp($bucket['Name'], $this->aws_bucket) == 0) {

                // Get the file name
                $file_name = basename( $this->video->getVideo() );

                $video_url = $this->video->getVideo();
                $s3_url = basename( $video_url );
                $video_name = explode(".", $s3_url);
                $s3_url_webm = $video_name[0]."-converted.webm";
                $s3_url_x264 = $video_name[0]."-converted.mp4";
                $s3_url_jpg = $video_name[0]."-converted.jpg";

                // Set the bucket folder
                $bucket_folder = $this->video->getUser()->getSecretId() . "/";
                if($this->video->getProject() != NULL)
                    $bucket_folder .= $this->video->getProject()->getSecretId() . "/";

                // Delete the file to S3
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder.$s3_url
                ));

                // Delete the webm video
                $s3_delete_webm = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder.$s3_url_webm
                ));

                // Delete the x264 video
                $s3_delete_x264 = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder.$s3_url_x264
                ));

                // Delete the screenshot
                $s3_delete_jpg = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder.$s3_url_jpg
                ));
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteVideoCommentsWithParent()
    {
        $comments = new Comment;
        $comments = $this->em->getRepository('StrimeAPIVideoBundle:Comment')->findBy(array('video' => $this->video));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

                // Delete files from Amazon
                if($comment->getS3Url() != NULL) {

                    $comment_action = $this->container->get('strime_api.helpers.comment_action');
                    $comment_action->aws_key = $this->container->getParameter('aws_key');
                    $comment_action->aws_secret = $this->container->getParameter('aws_secret');
                    $comment_action->aws_region = $this->container->getParameter('aws_region');
                    $comment_action->aws_bucket = $this->container->getParameter('aws_bucket_comments');
                    $comment_action->comment = $comment;
                    $comment_action->deleteCommentScreenshotFromAmazon();
                }

                // Check if the comment is listed as in error
                $comment_in_error = $this->em->getRepository('StrimeAPIVideoBundle:CommentWithThumbInError')->findBy(array('comment' => $comment));

                // If he is listed as in error
                if($comment_in_error != NULL) {

                    // Delete these entities
                    if(is_array($comment_in_error)) {
                        foreach ($comment_in_error as $comment_in_error_obj) {
                            $this->em->remove($comment_in_error_obj);
                        }
                    }
                    else {
                        $this->em->remove($comment_in_error);
                    }

                    $this->em->flush();
                }

                // Delete the entity
                if($comment->getAnswerTo() != NULL) {
                    $this->em->remove($comment);
                    $this->em->flush();
                }
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteVideoCommentsWithoutParent()
    {
        $comments = new Comment;
        $comments = $this->em->getRepository('StrimeAPIVideoBundle:Comment')->findBy(array('video' => $this->video));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

                // Delete files from Amazon
                if($comment->getS3Url() != NULL) {

                    $comment_action = $this->container->get('strime_api.helpers.comment_action');
                    $comment_action->aws_key = $this->container->getParameter('aws_key');
                    $comment_action->aws_secret = $this->container->getParameter('aws_secret');
                    $comment_action->aws_region = $this->container->getParameter('aws_region');
                    $comment_action->aws_bucket = $this->container->getParameter('aws_bucket_comments');
                    $comment_action->comment = $comment;
                    $comment_action->deleteCommentScreenshotFromAmazon();
                }

                // Check if the comment is listed as in error
                $comment_in_error = $this->em->getRepository('StrimeAPIVideoBundle:CommentWithThumbInError')->findBy(array('comment' => $comment));

                // If he is listed as in error
                if($comment_in_error != NULL) {

                    // Delete these entities
                    if(is_array($comment_in_error)) {
                        foreach ($comment_in_error as $comment_in_error_obj) {
                            $this->em->remove($comment_in_error_obj);
                        }
                    }
                    else {
                        $this->em->remove($comment_in_error);
                    }

                    $this->em->flush();
                }

                // Delete the entity
                $this->em->remove($comment);
                $this->em->flush();
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function updateUserSpaceAvailable()
    {
        $user = $this->video->getUser();
        $new_storage_used = $user->getStorageUsed() - $this->video->getSize();
        $user->setStorageUsed($new_storage_used);
        $this->em->persist($user);
        $this->em->flush();

        return;
    }



    /**
     * @return null
     */
    public function deleteAssociatedEncodingJobs()
    {
        $encoding_jobs = new EncodingJob;
        $encoding_jobs = $this->em->getRepository('StrimeAPIVideoBundle:EncodingJob')->findBy(array('video' => $this->video));

        if( is_array($encoding_jobs) ) {
            foreach ($encoding_jobs as $encoding_job) {

                $this->em->remove($encoding_job);
                $this->em->flush();
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteAssociatedEncodingJobsTimes()
    {
        $encoding_jobs_times = new EncodingJobTime;
        $encoding_jobs_times = $this->em->getRepository('StrimeAPIStatsBundle:EncodingJobTime')->findBy(array('video' => $this->video));

        if( is_array($encoding_jobs_times) ) {
            foreach ($encoding_jobs_times as $encoding_job_time) {

                $encoding_job_time->setVideo(NULL);
                $this->em->persist($encoding_job_time);
                $this->em->flush();
            }
        }
        elseif( is_object($encoding_jobs_times) ) {

            $encoding_jobs_times->setVideo(NULL);
            $this->em->persist($encoding_jobs_times);
            $this->em->flush();
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteAssociatedContacts()
    {
        $contacts = $this->video->getContacts();

        if(($contacts != NULL) && (is_array($contacts))) {
            foreach ($contacts as $contact) {
                $this->video->removeContact($contact);
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteYoutubeIds()
    {
        $videos_youtube = new VideoYoutube;
        $videos_youtube = $this->em->getRepository('StrimeAPIVideoBundle:VideoYoutube')->findBy(array('video' => $this->video));

        if( is_array($videos_youtube) ) {
            foreach ($videos_youtube as $youtube_item) {

                $this->em->remove($youtube_item);
                $this->em->flush();
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function moveVideoOnAmazon()
    {
        // Instantiate the S3 client using your credential profile
        $aws = S3Client::factory(array(
            'credentials' => array(
                'key'       => $this->aws_key,
                'secret'    => $this->aws_secret
            ),
            'version' => 'latest',
            'region' => $this->aws_region
        ));

        // Get client instances from the service locator by name
        // $s3Client = $aws->get('s3');

        // Get the buckets list
        $buckets_list = $aws->listBuckets();


        // Delete the file from Amazon S3
        foreach ($buckets_list['Buckets'] as $bucket) {

            if(strcmp($bucket['Name'], $this->aws_bucket) == 0) {

                // Get the file name
                $file_name = basename( $this->video->getVideo() );

                $video_url = $this->video->getVideo();
                $s3_url = basename( $video_url );
                $video_name = explode(".", $s3_url);
                $s3_url_webm = $video_name[0]."-converted.webm";
                $s3_url_x264 = $video_name[0]."-converted.mp4";
                $s3_url_jpg = $video_name[0]."-converted.jpg";

                // Set the bucket folder
                $old_bucket_folder = $this->video->getUser()->getSecretId() . "/";
                if($this->old_project_id != NULL)
                    $old_bucket_folder .= $this->old_project_id . "/";

                $new_bucket_folder = $this->video->getUser()->getSecretId() . "/";
                if($this->video->getProject() != NULL)
                    $new_bucket_folder .= $this->video->getProject()->getSecretId() . "/";

                // Copy the files from the old folder to the new one
                $s3_copy = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$s3_url,
                    'Key'        => $new_bucket_folder.$s3_url
                ));
                $s3_copy_webm = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$s3_url_webm,
                    'Key'        => $new_bucket_folder.$s3_url_webm
                ));
                $s3_copy_x264 = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$s3_url_x264,
                    'Key'        => $new_bucket_folder.$s3_url_x264
                ));
                $s3_copy_jpg = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$s3_url_jpg,
                    'Key'        => $new_bucket_folder.$s3_url_jpg
                ));

                // Delete the files from the original folder
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$s3_url
                ));
                $s3_delete_webm = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$s3_url_webm
                ));
                $s3_delete_x264 = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$s3_url_x264
                ));
                $s3_delete_jpg = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$s3_url_jpg
                ));

                // Update the URL of the screenshot and the URL of the video
                $s3_https_url_screenshot = $s3_copy_jpg['ObjectURL'];
                $s3_https_url = $s3_copy['ObjectURL'];

                $this->video->setS3ScreenshotUrl( $s3_https_url_screenshot );
                $this->video->setS3Url( $s3_https_url );
                $this->em->persist( $this->video );
                $this->em->flush();
            }
        }

        // Get all the comments of the video
        $comments = $this->em->getRepository('StrimeAPIVideoBundle:Comment')->findBy(array('video' => $this->video));

        // If there are comments
        if($comments != NULL) {

            // Set the comments helper
            $comment_action = $this->container->get('strime_api.helpers.comment_action');
            $comment_action->aws_key = $this->container->getParameter('aws_key');
            $comment_action->aws_secret = $this->container->getParameter('aws_secret');
            $comment_action->aws_region = $this->container->getParameter('aws_region');
            $comment_action->aws_bucket = $this->container->getParameter('aws_bucket_comments');
            $comment_action->old_project_id = $this->old_project_id;

            // Move all the comments
            foreach ($comments as $comment) {

                $comment_action->comment = $comment;
                $comment_action->moveCommentOnAmazon();
            }
        }

        return;
    }

}
