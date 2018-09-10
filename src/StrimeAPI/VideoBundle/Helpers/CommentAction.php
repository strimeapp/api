<?php

namespace StrimeAPI\VideoBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

use StrimeAPI\VideoBundle\Entity\Comment;
use StrimeAPI\VideoBundle\Entity\CommentWithThumbInError;
use Aws\S3\S3Client;

class CommentAction {

    public $aws_key;
    public $aws_secret;
    public $aws_region;
    public $aws_bucket;
    public $comment;
    public $old_project_id;
    private $container;
    protected $em;



    public function __construct(EntityManager $em, Container $container) {
        $this->em = $em;
        $this->container = $container;
    }



    /**
     * @return null
     */
    public function deleteCommentScreenshotFromAmazon()
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

                // Set the bucket folder
                $bucket_folder = $this->comment->getVideo()->getUser()->getSecretId() . "/";
                if($this->comment->getVideo()->getProject() != NULL) {
                    $bucket_folder = $this->comment->getVideo()->getUser()->getSecretId() . "/" . $this->comment->getVideo()->getProject()->getSecretId() . "/";
                }

                // Delete the file from S3
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $bucket_folder."comment-".$this->comment->getSecretId().".jpg"
                ));
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function deleteCommentFromErrorsTable()
    {
        $comments = new CommentWithThumbInError;
        $comments = $this->em->getRepository('StrimeAPIVideoBundle:CommentWithThumbInError')->findBy(array('comment' => $this->comment));

        if($comments != NULL) {
            if( is_array($comments) ) {
                foreach ($comments as $comment_in_error_obj) {

                    // Delete the entity
                    $this->em->remove($comment_in_error_obj);
                    $this->em->flush();

                }
            }
            else {

                // Delete the entity
                $this->em->remove($comments);
                $this->em->flush();
            }
        }

        return;
    }



    /**
     * @return null
     */
    public function moveCommentOnAmazon()
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
                $s3_url = basename( $this->comment->getS3Url() );

                // Set the bucket folder
                $old_bucket_folder = $this->comment->getVideo()->getUser()->getSecretId() . "/";
                if($this->old_project_id != NULL)
                    $old_bucket_folder .= $this->old_project_id . "/";

                $new_bucket_folder = $this->comment->getVideo()->getUser()->getSecretId() . "/";
                if($this->comment->getVideo()->getProject() != NULL)
                    $new_bucket_folder .= $this->comment->getVideo()->getProject()->getSecretId() . "/";

                // Copy the files from the old folder to the new one
                $s3_copy = $aws->copyObject(array(
                    'Bucket'     => $bucket['Name'],
                    'CopySource' => $bucket['Name']."/".$old_bucket_folder.$s3_url,
                    'Key'        => $new_bucket_folder.$s3_url
                ));

                // Delete the files from the original folder
                $s3_delete = $aws->deleteObject(array(
                    'Bucket'     => $bucket['Name'],
                    'Key'        => $old_bucket_folder.$s3_url
                ));

                // Update the URL of the screenshot of the comment
                $s3_https_url = $s3_copy['ObjectURL'];

                $this->comment->setS3Url( $s3_https_url );
                $this->em->persist( $this->comment );
                $this->em->flush();
            }
        }

        return;
    }

}
