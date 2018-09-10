<?php

    namespace StrimeAPI\AudioBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_audio")
     */
    class Audio
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
        *   mimeTypes = {"audio/aiff", "audio/mpeg", "audio/x-pn-realaudio", "audio/mpeg3", "audio/mp3", "audio/mp4", "audio/ogg", "audio/wav", "audio/webm"},
        *   maxSizeMessage = "The maximum size of this file is 2000M.",
        *   mimeTypesMessage = "Only audio files are allowed."
        * )
        */
        private $file;

        /**
        * @ORM\Column(name="audio", type="string", length=255, nullable=true)
        */
        private $path;

        /**
        * @ORM\Column(name="s3_url", type="string", length=255, nullable=true)
        */
        private $s3_url;

        /**
        * @ORM\Column(name="s3_thumbnail_url", type="string", length=255, nullable=true)
        */
        private $s3_thumbnail_url;

        /**
        * @ORM\Column(name="size", type="integer", nullable=true)
        */
        private $size;

        /**
        * @ORM\Column(name="duration", type="float", nullable=false)
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
         * @return Audio
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
         * @return Audio
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
         * @return Audio
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
         * @return Audio
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
         * @return Audio
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
         * @return Audio
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
        * @return Audio
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
        * @return Audio
        */
        public function setAudio($path) {
            $this->path = $path;
            return $this;
        }

        /**
        * @return string
        */
        public function getAudio() {
            return $this->path;
        }

        /**
        * @param string $s3_url
        * @return Audio
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
        * @param string $s3_thumbnail_url
        * @return Audio
        */
        public function setS3ThumbnailUrl($s3_thumbnail_url) {
            $this->s3_thumbnail_url = $s3_thumbnail_url;
            return $this;
        }

        /**
        * @return string
        */
        public function getS3ThumbnailUrl() {
            return $this->s3_thumbnail_url;
        }

        /**
        * @param integer $size
        * @return Audio
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
        * @param float $duration
        * @return Audio
        */
        public function setDuration($duration) {
            $this->duration = $duration;
            return $this;
        }

        /**
        * @return float
        */
        public function getDuration() {
            return $this->duration;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Contact $contact
         * @return Audio
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
         * @return Audio
         */
        public function getContacts() {
            return $this->contacts;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Audio
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
         * @return Audio
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
            // when displaying uploaded audio in the view.

            // Create the folder if it doesn't exist.
            $directory = __DIR__.'/../../../../web/';
            $folder = 'uploads/audios/' . $this->getUser()->getSecretId() . '/';

            if(!file_exists($directory.'uploads/'))
                mkdir($directory.'uploads/', 0755, TRUE);

            if(!file_exists($directory.'uploads/audios/'))
                mkdir($directory.'uploads/audios/', 0755, TRUE);

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
            $directory = realpath( $this->getUploadRootDir() );
            // chmod( $directory . '/' . $this->getAudio(), 0777 );

            $this->file = NULL;
        }
    }
