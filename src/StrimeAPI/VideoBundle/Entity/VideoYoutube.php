<?php

    namespace StrimeAPI\VideoBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_video_youtube")
     */
    class VideoYoutube
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="youtube_id", type="string", length=200, nullable=false)
         */
        protected $youtube_id;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Video")
         */
        protected $video;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;

        /**
         * @ORM\Column(name="updated_at", type="datetime")
         */
        protected $updated_at;



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param string $youtube_id
         * @return VideoYoutube
         */
        public function setYoutubeId($youtube_id) {
            $this->youtube_id = $youtube_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getYoutubeId() {
            return $this->youtube_id;
        }

        /**
         * @param integer $video
         * @return VideoYoutube
         */
        public function setVideo($video) {
            $this->video = $video;
            return $this;
        }

        /**
         * @return integer
         */
        public function getVideo() {
            return $this->video;
        }

		/**
		 * @param \DateTime $created_at
		 * @return VideoYoutube
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
		 * @return VideoYoutube
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
    }
