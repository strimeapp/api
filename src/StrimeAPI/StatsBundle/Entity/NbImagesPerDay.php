<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats_nb_images_per_day")
     */
    class NbImagesPerDay
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="date_time", type="integer", nullable=false)
         */
        protected $date_time = 0;

        /**
         * @ORM\Column(name="nb_images", type="integer", nullable=false)
         */
        protected $nb_images = 0;

        /**
         * @ORM\Column(name="total_nb_images", type="integer", nullable=false)
         */
        protected $total_nb_images = 0;

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
         * @param integer $date_time
         * @return NbImagesPerDay
         */
        public function setDateTime($date_time) {
            $this->date_time = $date_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getDateTime() {
            return $this->date_time;
        }

        /**
         * @param integer $nb_images
         * @return NbImagesPerDay
         */
        public function setNbImages($nb_images) {
            $this->nb_images = $nb_images;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbImages() {
            return $this->nb_images;
        }

        /**
         * @param integer $total_nb_images
         * @return NbImagesPerDay
         */
        public function setTotalNbImages($total_nb_images) {
            $this->total_nb_images = $total_nb_images;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTotalNbImages() {
            return $this->total_nb_images;
        }

		/**
		 * @param \DateTime $created_at
		 * @return NbImagesPerDay
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
