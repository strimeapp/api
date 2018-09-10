<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_user_facebook")
     */
    class UserFacebook
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="facebook_id", type="string", length=30, nullable=false)
         */
        protected $facebook_id;

        /**
         * @ORM\Column(name="facebook_image", type="text", nullable=true)
         */
        protected $facebook_image;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\UserBundle\Entity\User")
         */
        protected $user;

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
         * @param string $facebook_id
         * @return UserFacebook
         */
        public function setFacebookId($facebook_id) {
            $this->facebook_id = $facebook_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getFacebookId() {
            return $this->facebook_id;
        }

        /**
         * @param string $facebook_image
         * @return UserFacebook
         */
        public function setFacebookImage($facebook_image) {
            $this->facebook_image = $facebook_image;
            return $this;
        }

        /**
         * @return string
         */
        public function getFacebookImage() {
            return $this->facebook_image;
        }

        /**
         * @param integer $user
         * @return UserFacebook
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
		 * @param \DateTime $created_at
		 * @return UserFacebook
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
		 * @return UserFacebook
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
