<?php

    namespace StrimeAPI\StatsBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_stats_encoding_job_time")
     */
    class EncodingJobTime
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Video")
         */
        protected $video;

        /**
         * @ORM\Column(name="start_time", type="integer", nullable=false)
         */
        protected $start_time = 0;

        /**
         * @ORM\Column(name="end_time", type="integer", nullable=false)
         */
        protected $end_time = 0;

        /**
         * @ORM\Column(name="total_time", type="integer", nullable=false)
         */
        protected $total_time = 0;

        /**
         * @ORM\Column(name="size", type="integer", nullable=true)
         */
        protected $size = 0;

        /**
         * @ORM\Column(name="duration", type="integer", nullable=false)
         */
        protected $duration = 0;

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
         * @param integer $video
         * @return EncodingJobTime
         */
        public function setVideo($video) {
            $this->video = $video;
            return $this;
        }

        /**
         * @return integer
         */
        public function getVideo() {
            return $this->video;
        }

        /**
         * @param integer $start_time
         * @return EncodingJobTime
         */
        public function setStartTime($start_time) {
            $this->start_time = $start_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getStartTime() {
            return $this->start_time;
        }

        /**
         * @param integer $end_time
         * @return EncodingJobTime
         */
        public function setEndTime($end_time) {
            $this->end_time = $end_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getEndTime() {
            return $this->end_time;
        }

        /**
         * @param integer $total_time
         * @return EncodingJobTime
         */
        public function setTotalTime($total_time) {
            $this->total_time = $total_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTotalTime() {
            return $this->total_time;
        }

        /**
         * @param integer $size
         * @return EncodingJob
         */
        public function setSize($size) {
            $this->size = $size;
            return $this;
        }

        /**
         * @return integer
         */
        public function getSize() {
            return $this->size;
        }

        /**
         * @param integer $duration
         * @return EncodingJob
         */
        public function setDuration($duration) {
            $this->duration = $duration;
            return $this;
        }

        /**
         * @return integer
         */
        public function getDuration() {
            return $this->duration;
        }

		/**
		 * @param \DateTime $created_at
		 * @return EncodingJobTime
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
         * @return EncodingJobTime
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
