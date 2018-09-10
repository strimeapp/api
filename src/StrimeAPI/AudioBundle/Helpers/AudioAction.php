<?php

namespace StrimeAPI\AudioBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

use StrimeAPI\AudioBundle\Entity\Audio;
use StrimeAPI\AudioBundle\Entity\AudioComment;
use StrimeAPI\UserBundle\Entity\Contact;
use StrimeAPI\AudioBundle\Entity\AudioEncodingJob;
use StrimeAPI\StatsBundle\Entity\AudioEncodingJobTime;
use Aws\S3\S3Client;

class AudioAction {

    public $aws_key;
    public $aws_secret;
    public $aws_region;
    public $aws_bucket;
    public $audio;
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
    public function deleteAudioFromAmazon()
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
                $audio_url = basename( $this->audio->getS3Url() );
                $audio_thumbnail_url = basename( $this->audio->getS3ThumbnailUrl() );

                // Set the bucket folder
                $bucket_folder = $this->audio->getUser()->getSecretId() . "/";
                if($this->audio->getProject() != NULL)
                    $bucket_folder .= $this->audio->getProject()->getSecretId() . "/";

                // Delete the file to S3
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder . $audio_url
                ));

                // Delete the thumbnails
                $s3_delete_thumbnail = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder . $audio_thumbnail_url
                ));
                $s3_delete_thumbnail_player = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder . str_replace(".png", "-player.png", $audio_thumbnail_url)
                ));
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteAudioCommentsWithParent()
    {
        $comments = new AudioComment;
        $comments = $this->em->getRepository('StrimeAPIAudioBundle:AudioComment')->findBy(array('audio' => $this->audio));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

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
    public function deleteAudioCommentsWithoutParent()
    {
        $comments = new AudioComment;
        $comments = $this->em->getRepository('StrimeAPIAudioBundle:AudioComment')->findBy(array('audio' => $this->audio));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

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
        $user = $this->audio->getUser();
        $new_storage_used = $user->getStorageUsed() - $this->audio->getSize();
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
        $encoding_jobs = new AudioEncodingJob;
        $encoding_jobs = $this->em->getRepository('StrimeAPIAudioBundle:AudioEncodingJob')->findBy(array('audio' => $this->audio));

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
        $encoding_jobs_times = new AudioEncodingJobTime;
        $encoding_jobs_times = $this->em->getRepository('StrimeAPIStatsBundle:AudioEncodingJobTime')->findBy(array('audio' => $this->audio));

        if( is_array($encoding_jobs_times) ) {
            foreach ($encoding_jobs_times as $encoding_job_time) {

                $encoding_job_time->setAudio(NULL);
                $this->em->persist($encoding_job_time);
                $this->em->flush();
            }
        }
        elseif( is_object($encoding_jobs_times) ) {

            $encoding_jobs_times->setAudio(NULL);
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
        $contacts = $this->audio->getContacts();

        if(($contacts != NULL) && (is_array($contacts))) {
            foreach ($contacts as $contact) {
                $this->audio->removeContact($contact);
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function moveAudioOnAmazon()
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

                $audio_url = $this->audio->getS3Url();
                $audio_thumbnail_url = $this->audio->getS3ThumbnailUrl();

                // Get the file name
                $file_name_thumbnail = basename( $audio_url );
                $file_name_thumbnail_player = str_replace(".png", "-player.png", $file_name_thumbnail);

                // Set the bucket folder
                $old_bucket_folder = $this->audio->getUser()->getSecretId() . "/";
                if($this->old_project_id != NULL)
                    $old_bucket_folder .= $this->old_project_id . "/";

                $new_bucket_folder = $this->audio->getUser()->getSecretId() . "/";
                if($this->audio->getProject() != NULL)
                    $new_bucket_folder .= $this->audio->getProject()->getSecretId() . "/";

                // Copy the files from the old folder to the new one
                $s3_copy = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$file_name,
                    'Key'        => $new_bucket_folder.$file_name
                ));
                $s3_copy_thumbnail = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$file_name_thumbnail,
                    'Key'        => $new_bucket_folder.$file_name_thumbnail
                ));
                $s3_copy_thumbnail_player = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$file_name_thumbnail_player,
                    'Key'        => $new_bucket_folder.$file_name_thumbnail_player
                ));

                // Delete the files from the original folder
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$file_name
                ));
                $s3_delete_thumbnail = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$file_name_thumbnail
                ));
                $s3_delete_thumbnail_player = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$file_name_thumbnail_player
                ));

                // Update the URL of the screenshot and the URL of the audio file
                $s3_https_url_thumbnail = $s3_copy_thumbnail['ObjectURL'];
                $s3_https_url = $s3_copy['ObjectURL'];

                $this->audio->setS3ThumbnailUrl( $s3_https_url_thumbnail );
                $this->audio->setS3Url( $s3_https_url );
                $this->em->persist( $this->audio );
                $this->em->flush();
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function checkIfFileExistsOnAmazon($file)
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

                $response = $aws->doesObjectExist($this->aws_bucket, $file);
                $logger = $this->container->get("logger");
                $logger->info("File exists: ".var_export($response, TRUE));
            }
        }

        return;
    }

}
