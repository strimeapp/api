<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats_nb_projects_per_day")
     */
    class NbProjectsPerDay
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
         * @ORM\Column(name="nb_projects", type="integer", nullable=false)
         */
        protected $nb_projects = 0;

        /**
         * @ORM\Column(name="total_nb_projects", type="integer", nullable=false)
         */
        protected $total_nb_projects = 0;

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
         * @return NbProjectsPerDay
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
         * @param integer $nb_projects
         * @return NbProjectsPerDay
         */
        public function setNbProjects($nb_projects) {
            $this->nb_projects = $nb_projects;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbProjects() {
            return $this->nb_projects;
        }

        /**
         * @param integer $total_nb_projects
         * @return NbProjectsPerDay
         */
        public function setTotalNbProjects($total_nb_projects) {
            $this->total_nb_projects = $total_nb_projects;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTotalNbProjects() {
            return $this->total_nb_projects;
        }

		/**
		 * @param \DateTime $created_at
		 * @return NbProjectsPerDay
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
