<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats_nb_contacts_per_day")
     */
    class NbContactsPerDay
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
         * @ORM\Column(name="nb_contacts", type="integer", nullable=false)
         */
        protected $nb_contacts = 0;

        /**
         * @ORM\Column(name="total_nb_contacts", type="integer", nullable=false)
         */
        protected $total_nb_contacts = 0;

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
         * @return NbContactsPerDay
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
         * @param integer $nb_contacts
         * @return NbContactsPerDay
         */
        public function setNbContacts($nb_contacts) {
            $this->nb_contacts = $nb_contacts;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbContacts() {
            return $this->nb_contacts;
        }

        /**
         * @param integer $total_nb_contacts
         * @return NbContactsPerDay
         */
        public function setTotalNbContacts($total_nb_contacts) {
            $this->total_nb_contacts = $total_nb_contacts;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTotalNbContacts() {
            return $this->total_nb_contacts;
        }

		/**
		 * @param \DateTime $created_at
		 * @return NbContactsPerDay
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
