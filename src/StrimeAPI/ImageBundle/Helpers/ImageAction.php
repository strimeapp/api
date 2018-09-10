<?php

namespace StrimeAPI\ImageBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

use StrimeAPI\ImageBundle\Entity\Image;
use StrimeAPI\ImageBundle\Entity\ImageComment;
use StrimeAPI\UserBundle\Entity\Contact;
use Aws\S3\S3Client;

class ImageAction {

    public $aws_key;
    public $aws_secret;
    public $aws_region;
    public $aws_bucket;
    public $image;
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
    public function deleteImageFromAmazon()
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
                $image_url = basename( $this->image->getS3Url() );
                $image_thumbnail_url = basename( $this->image->getS3ThumbnailUrl() );

                // Set the bucket folder
                $bucket_folder = $this->image->getUser()->getSecretId() . "/";
                if($this->image->getProject() != NULL)
                    $bucket_folder .= $this->image->getProject()->getSecretId() . "/";

                // Delete the file to S3
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder . $image_url
                ));

                // Delete the thumbnail
                $s3_delete_thumbnail = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder . $image_thumbnail_url
                ));
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteImageCommentsWithParent()
    {
        $comments = new ImageComment;
        $comments = $this->em->getRepository('StrimeAPIImageBundle:ImageComment')->findBy(array('image' => $this->image));

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
    public function deleteImageCommentsWithoutParent()
    {
        $comments = new ImageComment;
        $comments = $this->em->getRepository('StrimeAPIImageBundle:ImageComment')->findBy(array('image' => $this->image));

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
        $user = $this->image->getUser();
        $new_storage_used = $user->getStorageUsed() - $this->image->getSize();
        $user->setStorageUsed($new_storage_used);
        $this->em->persist($user);
        $this->em->flush();

        return;
    }



    /**
     * @return null
     */
    public function deleteAssociatedContacts()
    {
        $contacts = $this->image->getContacts();

        if(($contacts != NULL) && (is_array($contacts))) {
            foreach ($contacts as $contact) {
                $this->image->removeContact($contact);
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function moveImageOnAmazon()
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

                $image_url = $this->image->getS3Url();
                $image_thumbnail_url = $this->image->getS3ThumbnailUrl();

                // Get the file name
                $file_name = basename( $image_url );
                $image_name = explode(".", $file_name);
                $file_name_thumbnail = $image_name[0]."-thumbnail.jpg";

                // Set the bucket folder
                $old_bucket_folder = $this->image->getUser()->getSecretId() . "/";
                if($this->old_project_id != NULL)
                    $old_bucket_folder .= $this->old_project_id . "/";

                $new_bucket_folder = $this->image->getUser()->getSecretId() . "/";
                if($this->image->getProject() != NULL)
                    $new_bucket_folder .= $this->image->getProject()->getSecretId() . "/";

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

                // Delete the files from the original folder
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$file_name
                ));
                $s3_delete_thumbnail = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$file_name_thumbnail
                ));

                // Update the URL of the screenshot and the URL of the image
                $s3_https_url_thumbnail = $s3_copy_thumbnail['ObjectURL'];
                $s3_https_url = $s3_copy['ObjectURL'];

                $this->image->setS3ThumbnailUrl( $s3_https_url_thumbnail );
                $this->image->setS3Url( $s3_https_url );
                $this->em->persist( $this->image );
                $this->em->flush();
            }
        }

        return;
    }

}
