<?php

    namespace StrimeAPI\VideoBundle\Entity;

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_project")
     */
    class Project
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
         * @ORM\Column(name="name", type="string", length=200, nullable=false)
         */
        protected $name;

        /**
         * @ORM\Column(name="description", type="text", nullable=true)
         */
        protected $description;

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
         * @return Project
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
         * @return Project
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
         * @param string $name
         * @return Project
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
         * @param string $description
         * @return Project
         */
        public function setDescription($description) {
            $this->description = $description;
            return $this;
        }

        /**
         * @return string
         */
        public function getDescription() {
            return $this->description;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Contact $contact
         * @return Project
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
         * @return Project
         */
        public function getContacts() {
            return $this->contacts;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Project
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
         * @return Project
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