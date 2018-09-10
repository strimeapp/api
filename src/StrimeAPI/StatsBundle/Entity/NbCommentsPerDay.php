<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats_nb_comments_per_day")
     */
    class NbCommentsPerDay
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
         * @ORM\Column(name="nb_comments", type="integer", nullable=false)
         */
        protected $nb_comments = 0;

        /**
         * @ORM\Column(name="total_nb_comments", type="integer", nullable=false)
         */
        protected $total_nb_comments = 0;

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
         * @return NbCommentsPerDay
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
         * @param integer $nb_comments
         * @return NbCommentsPerDay
         */
        public function setNbComments($nb_comments) {
            $this->nb_comments = $nb_comments;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbComments() {
            return $this->nb_comments;
        }

        /**
         * @param integer $total_nb_comments
         * @return NbCommentsPerDay
         */
        public function setTotalNbComments($total_nb_comments) {
            $this->total_nb_comments = $total_nb_comments;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTotalNbComments() {
            return $this->total_nb_comments;
        }

		/**
		 * @param \DateTime $created_at
		 * @return NbCommentsPerDay
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
