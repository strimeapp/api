<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_contact")
     */
    class Contact
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
         * @ORM\Column(name="email", type="string", length=200, nullable=false)
         */
        protected $email;

        /**
         * @ORM\Column(name="avatar", type="string", length=200, nullable=false)
         */
        protected $avatar;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param string $secret_id
         * @return Contact
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
         * @return Contact
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
         * @param string $email
         * @return Contact
         */
        public function setEmail($email) {
            $this->email = $email;
            return $this;
        }

        /**
         * @return string
         */
        public function getEmail() {
            return $this->email;
        }

        /**
         * @param string $avatar
         * @return Contact
         */
        public function setAvatar($avatar) {
            $this->avatar = $avatar;
            return $this;
        }

        /**
         * @return string
         */
        public function getAvatar() {
            return $this->avatar;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Contact
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
	     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
	     *
	     * @ORM\PrePersist
	     * @ORM\PreUpdate
	     */
	    public function updatedTimestamps() {

	        if($this->getCreatedAt() == null)
	        {
	            $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
	        }
	    }
    }