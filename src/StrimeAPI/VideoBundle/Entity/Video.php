<?php

    namespace StrimeAPI\VideoBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_video")
     */
    class Video
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="secret_id", type="string", length=10, nullable=false)
         */
        protected $secret_id;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\UserBundle\Entity\User")
         */
        protected $user;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Project")
         */
        protected $project;

        /**
         * @ORM\Column(name="name", type="string", length=200, nullable=false)
         */
        protected $name;

        /**
         * @ORM\Column(name="description", type="text", nullable=true)
         */
        protected $description;

        /**
         * @ORM\Column(name="password", type="string", length=200, nullable=true)
         */
        protected $password;

        /**
        * @Assert\File(
        *   maxSize="2000M",
        *   mimeTypes = {"video/3gpp", "video/H264", "video/H265", "video/mp4", "video/mpeg", "video/ogg", "video/quicktime", "video/x-flv", "application/x-mpegURL", "video/MP2T", "video/x-msvideo"},
        *   maxSizeMessage = "The maximum size for this file is 2000M.",
        *   mimeTypesMessage = "Only videos are allowed."
        * )
        */
        private $file;

        /**
        * @ORM\Column(name="video", type="string", length=255, nullable=true)
        */
        private $path;

        /**
        * @ORM\Column(name="s3_url", type="string", length=255, nullable=true)
        */
        private $s3_url;

        /**
        * @ORM\Column(name="s3_screenshot_url", type="string", length=255, nullable=true)
        */
        private $s3_screenshot_url;

        /**
        * @ORM\Column(name="size", type="integer", nullable=true)
        */
        private $size;

        /**
        * @ORM\Column(name="duration", type="float", nullable=true)
        */
        private $duration = 0;

        /**
         * @ORM\ManyToMany(targetEntity="StrimeAPI\UserBundle\Entity\Contact", cascade={"persist"})
         */
        protected $contacts;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;

        /**
         * @ORM\Column(name="updated_at", type="datetime")
         */
        protected $updated_at;




        public function __construct() {
            $this->contacts = new ArrayCollection();
        }



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param string $secret_id
         * @return Video
         */
        public function setSecretId($secret_id) {
            $this->secret_id = $secret_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getSecretId() {
            return $this->secret_id;
        }

        /**
         * @param integer $user
         * @return Video
         */
        public function setUser($user) {
            $this->user = $user;
            return $this;
        }

        /**
         * @return integer
         */
        public function getUser() {
            return $this->user;
        }

        /**
         * @param integer $project
         * @return Video
         */
        public function setProject($project) {
            $this->project = $project;
            return $this;
        }

        /**
         * @return integer
         */
        public function getProject() {
            return $this->project;
        }

        /**
         * @param string $name
         * @return Video
         */
        public function setName($name) {
            $this->name = $name;
            return $this;
        }

        /**
         * @return string
         */
        public function getName() {
            return $this->name;
        }

        /**
         * @param text $description
         * @return Video
         */
        public function setDescription($description) {
            $this->description = $description;
            return $this;
        }

        /**
         * @return text
         */
        public function getDescription() {
            return $this->description;
        }

        /**
         * @param string $password
         * @return Video
         */
        public function setPassword($password) {
            $this->password = $password;
            return $this;
        }

        /**
         * @return string
         */
        public function getPassword() {
            return $this->password;
        }

        /**
        * @param UploadedFile $file
        * @return Video
        */
        public function setFile(UploadedFile $file = null) {
            $this->file = $file;
            return $this;
        }

        /**
        * @return UploadedFile
        */
        public function getFile() {
            return $this->file;
        }

        /**
        * @param string $path
        * @return Video
        */
        public function setVideo($path) {
            $this->path = $path;
            return $this;
        }

        /**
        * @return string
        */
        public function getVideo() {
            return $this->path;
        }

        /**
        * @param string $s3_url
        * @return Video
        */
        public function setS3Url($s3_url) {
            $this->s3_url = $s3_url;
            return $this;
        }

        /**
        * @return string
        */
        public function getS3Url() {
            return $this->s3_url;
        }

        /**
        * @param string $s3_screenshot_url
        * @return Video
        */
        public function setS3ScreenshotUrl($s3_screenshot_url) {
            $this->s3_screenshot_url = $s3_screenshot_url;
            return $this;
        }

        /**
        * @return string
        */
        public function getS3ScreenshotUrl() {
            return $this->s3_screenshot_url;
        }

        /**
        * @param integer $size
        * @return Video
        */
        public function setSize($size) {
            $this->size = $size;
            return $this;
        }

        /**
        * @return integer
        */
        public function getSize() {
            return $this->size;
        }

        /**
        * @param integer $duration
        * @return Video
        */
        public function setDuration($duration) {
            $this->duration = $duration;
            return $this;
        }

        /**
        * @return integer
        */
        public function getDuration() {
            return $this->duration;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Contact $contact
         * @return Video
         */
        public function addContact(\StrimeAPI\UserBundle\Entity\Contact $contact) {
            $this->contacts[] = $contact;
            return $this;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Contact $contact
         */
        public function removeContact(\StrimeAPI\UserBundle\Entity\Contact $contact) {
            $this->contacts->removeElement($contact);
        }

        /**
         * @return Video
         */
        public function getContacts() {
            return $this->contacts;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Video
		 */
		public function setCreatedAt($created_at) {
			$this->created_at = $created_at;
			return $this;
		}

	    /**
	     * @return \DateTime
	     */
	    public function getCreatedAt() {
	    	return $this->created_at;
		}

        /**
         * @param \DateTime $updated_at
         * @return Video
         */
        public function setUpdatedAt($updated_at) {
            $this->updated_at = $updated_at;
            return $this;
        }

        /**
         * @return \DateTime
         */
        public function getUpdatedAt() {
            return $this->updated_at;
        }

	    /**
	     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
	     *
	     * @ORM\PrePersist
	     * @ORM\PreUpdate
	     */
	    public function updatedTimestamps() {
            $this->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));

            if($this->getCreatedAt() == null)
            {
                $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            }
	    }


        /**
        *
        * Deal with the upload.
        *
        */

        /**
        * @return string
        */
        public function getAbsolutePath() {
            return null === $this->path
                ? null
                : $this->getUploadRootDir().'/'.$this->path;
        }

        /**
        * @return string
        */
        public function getPath() {
            return null === $this->path
                ? null
                : $this->path;
        }

        /**
        * @return string
        */
        public function getWebPath() {
            return null === $this->path
                ? null
                : $this->getUploadDir().'/'.$this->path;
        }

        protected function getUploadRootDir() {
            // the absolute directory path where uploaded
            // documents should be saved
            return __DIR__.'/../../../../web/'.$this->getUploadDir();
        }

        protected function getUploadDir() {
            // get rid of the __DIR__ so it doesn't screw up
            // when displaying uploaded doc/image in the view.

            // Create the folder if it doesn't exist.
            $directory = __DIR__.'/../../../../web/';
            $folder = 'uploads/videos/' . $this->getUser()->getSecretId() . '/';

            if(!file_exists($directory.'uploads/'))
                mkdir($directory.'uploads/', 0755, TRUE);

            if(!file_exists($folder))
                mkdir($directory.$folder, 0755, TRUE);

            return $folder;
        }

        /**
         * @ORM\PrePersist()
         * @ORM\PreUpdate()
         */
        public function preUpload() {
            if (null !== $this->file) {
                // do whatever you want to generate a unique name
                $filename = sha1(uniqid(mt_rand(), true));

                if($this->file->guessExtension() !== "") {
                    $this->path = $this->getUploadDir().$filename.'.'.$this->file->guessExtension();
                }
                else {
                    $this->path = $this->getUploadDir().$filename;
                }
            }
        }

        /**
         * @ORM\PostPersist()
         * @ORM\PostUpdate()
         */
        public function upload() {
            if (null === $this->file) {
                return;
            }

            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->file->move($this->getUploadRootDir(), $this->path);

            // Change the rights on the file
            $directory = realpath( __DIR__.'/../../../../web/' );
            chmod( $directory . '/' . $this->getVideo(), 0777 );

            $this->file = NULL;
        }
    }
