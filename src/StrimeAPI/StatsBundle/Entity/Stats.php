<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats")
     */
    class Stats
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="name", type="string", length=200, nullable=false)
         */
        protected $name;

        /**
         * @ORM\Column(name="data", type="float", nullable=true)
         */
        protected $data;

        /**
         * @ORM\Column(name="data_json", type="text", nullable=true)
         */
        protected $data_json;

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
         * @param string $name
         * @return Stats
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
         * @param float $data
         * @return Stats
         */
        public function setData($data) {
            $this->data = $data;
            return $this;
        }

        /**
         * @return float
         */
        public function getData() {
            return $this->data;
        }

        /**
         * @param text $data_json
         * @return Stats
         */
        public function setDataJson($data_json) {
            $this->data_json = $data_json;
            return $this;
        }

        /**
         * @return text
         */
        public function getDataJson() {
            return $this->data_json;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Stats
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
         * @return Stats
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