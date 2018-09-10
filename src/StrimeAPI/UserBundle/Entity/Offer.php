<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_offer")
     */
    class Offer
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
         * @ORM\Column(name="name", type="string", length=200, nullable=false)
         */
        protected $name;

        /**
         * @ORM\Column(name="price", type="float", nullable=false)
         */
        protected $price;

        /**
         * @ORM\Column(name="storage_allowed", type="float", nullable=false)
         */
        protected $storage_allowed;

        /**
         * @ORM\Column(name="nb_videos", type="integer", nullable=false)
         */
        protected $nb_videos = 0;

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
         * @param string $secret_id
         * @return Offer
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
         * @param string $name
         * @return Offer
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
         * @param float $price
         * @return Offer
         */
        public function setPrice($price) {
            $this->price = $price;
            return $this;
        }

        /**
         * @return float
         */
        public function getPrice() {
            return $this->price;
        }

        /**
         * @param float $storage_allowed
         * @return Offer
         */
        public function setStorageAllowed($storage_allowed) {
            $this->storage_allowed = $storage_allowed;
            return $this;
        }

        /**
         * @return float
         */
        public function getStorageAllowed() {
            return $this->storage_allowed;
        }

        /**
         * @param integer $nb_videos
         * @return Offer
         */
        public function setNbVideos($nb_videos) {
            $this->nb_videos = $nb_videos;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbVideos() {
            return $this->nb_videos;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Offer
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
		 * @return Offer
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