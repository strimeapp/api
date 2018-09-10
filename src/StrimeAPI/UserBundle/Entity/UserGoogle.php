<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_user_google")
     */
    class UserGoogle
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="google_id", type="string", length=30, nullable=false)
         */
        protected $google_id;

        /**
         * @ORM\Column(name="google_image", type="string", length=200, nullable=true)
         */
        protected $google_image;

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
         * @param string $google_id
         * @return UserGoogle
         */
        public function setGoogleId($google_id) {
            $this->google_id = $google_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getGoogleId() {
            return $this->google_id;
        }

        /**
         * @param string $google_image
         * @return UserGoogle
         */
        public function setGoogleImage($google_image) {
            $this->google_image = $google_image;
            return $this;
        }

        /**
         * @return string
         */
        public function getGoogleImage() {
            return $this->google_image;
        }

        /**
         * @param integer $user
         * @return UserGoogle
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
		 * @return UserGoogle
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
		 * @return UserGoogle
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
