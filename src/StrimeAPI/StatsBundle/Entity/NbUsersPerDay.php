<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats_nb_users_per_day")
     */
    class NbUsersPerDay
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
         * @ORM\Column(name="nb_users", type="integer", nullable=false)
         */
        protected $nb_users = 0;

        /**
         * @ORM\Column(name="total_nb_users", type="integer", nullable=false)
         */
        protected $total_nb_users = 0;

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
         * @return NbUsersPerDay
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
         * @param integer $nb_users
         * @return NbUsersPerDay
         */
        public function setNbUsers($nb_users) {
            $this->nb_users = $nb_users;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbUsers() {
            return $this->nb_users;
        }

        /**
         * @param integer $total_nb_users
         * @return NbUsersPerDay
         */
        public function setTotalNbUsers($total_nb_users) {
            $this->total_nb_users = $total_nb_users;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTotalNbUsers() {
            return $this->total_nb_users;
        }

		/**
		 * @param \DateTime $created_at
		 * @return NbUsersPerDay
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