<?php

    namespace StrimeAPI\AudioBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_audio_encoding_job")
     */
    class AudioEncodingJob
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
         * @ORM\ManyToOne(targetEntity="StrimeAPI\AudioBundle\Entity\Audio")
         */
        protected $audio;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Project")
         */
        protected $project;

        /**
         * @ORM\Column(name="status", type="integer", nullable=false)
         */
        protected $status = 0;

        /**
         * @ORM\Column(name="error_code", type="integer", nullable=true)
         */
        protected $error_code;

        /**
         * @ORM\Column(name="started", type="integer", nullable=false)
         */
        protected $started = 0;

        /**
         * @ORM\Column(name="filename", type="string", length=200, nullable=true)
         */
        protected $filename;

        /**
         * @ORM\Column(name="upload_path", type="string", length=200, nullable=true)
         */
        protected $upload_path;

        /**
         * @ORM\Column(name="full_audio_path", type="string", length=200, nullable=true)
         */
        protected $full_audio_path;

        /**
         * @ORM\Column(name="restarted", type="integer", nullable=false)
         */
        protected $restarted = 0;

        /**
         * @ORM\Column(name="restart_time", type="integer", nullable=false)
         */
        protected $restart_time = 0;

        /**
         * @ORM\Column(name="estimated_time_in_queue", type="integer", nullable=false)
         */
        protected $estimated_time_in_queue = 0;

        /**
         * @ORM\Column(name="estimated_encoding_time", type="integer", nullable=false)
         */
        protected $estimated_encoding_time = 0;

        /**
         * @ORM\Column(name="encoding_server", type="string", length=60, nullable=true)
         */
        protected $encoding_server;

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
         * @return AudioEncodingJob
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
         * @return AudioEncodingJob
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
         * @param integer $audio
         * @return AudioEncodingJob
         */
        public function setAudio($audio) {
            $this->audio = $audio;
            return $this;
        }

        /**
         * @return integer
         */
        public function getAudio() {
            return $this->audio;
        }

        /**
         * @param integer $project
         * @return AudioEncodingJob
         */
        public function setProject($project) {
            $this->project = $project;
            return $this;
        }

        /**
         * @return integer
         */
        public function getProject() {
            return $this->project;
        }

        /**
         * @param integer $status
         * @return AudioEncodingJob
         */
        public function setStatus($status) {
            $this->status = $status;
            return $this;
        }

        /**
         * @return integer
         */
        public function getStatus() {
            return $this->status;
        }

        /**
         * @param integer $error_code
         * @return AudioEncodingJob
         */
        public function setErrorCode($error_code) {
            $this->error_code = $error_code;
            return $this;
        }

        /**
         * @return integer
         */
        public function getErrorCode() {
            return $this->error_code;
        }

        /**
         * @param integer $started
         * @return AudioEncodingJob
         */
        public function setStarted($started) {
            $this->started = $started;
            return $this;
        }

        /**
         * @return integer
         */
        public function getStarted() {
            return $this->started;
        }

        /**
         * @param string $filename
         * @return AudioEncodingJob
         */
        public function setFilename($filename) {
            $this->filename = $filename;
            return $this;
        }

        /**
         * @return string
         */
        public function getFilename() {
            return $this->filename;
        }

        /**
         * @param string $upload_path
         * @return AudioEncodingJob
         */
        public function setUploadPath($upload_path) {
            $this->upload_path = $upload_path;
            return $this;
        }

        /**
         * @return string
         */
        public function getUploadPath() {
            return $this->upload_path;
        }

        /**
         * @param string $full_audio_path
         * @return AudioEncodingJob
         */
        public function setFullAudioPath($full_audio_path) {
            $this->full_audio_path = $full_audio_path;
            return $this;
        }

        /**
         * @return string
         */
        public function getFullAudioPath() {
            return $this->full_audio_path;
        }

        /**
         * @param integer $estimated_time_in_queue
         * @return AudioEncodingJob
         */
        public function setEstimatedTimeInQueue($estimated_time_in_queue) {
            $this->estimated_time_in_queue = $estimated_time_in_queue;
            return $this;
        }

        /**
         * @return integer
         */
        public function getEstimatedTimeInQueue() {
            return $this->estimated_time_in_queue;
        }

        /**
         * @param integer $estimated_encoding_time
         * @return AudioEncodingJob
         */
        public function setEstimatedEncodingTime($estimated_encoding_time) {
            $this->estimated_encoding_time = $estimated_encoding_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getEstimatedEncodingTime() {
            return $this->estimated_encoding_time;
        }

        /**
         * @param integer $restarted
         * @return AudioEncodingJob
         */
        public function setRestarted($restarted) {
            $this->restarted = $restarted;
            return $this;
        }

        /**
         * @return integer
         */
        public function getRestarted() {
            return $this->restarted;
        }

        /**
         * @param integer $restart_time
         * @return AudioEncodingJob
         */
        public function setRestartTime($restart_time) {
            $this->restart_time = $restart_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getRestartTime() {
            return $this->restart_time;
        }

        /**
         * @param string $encoding_server
         * @return AudioEncodingJob
         */
        public function setEncodingServer($encoding_server) {
            $this->encoding_server = $encoding_server;
            return $this;
        }

        /**
         * @return string
         */
        public function getEncodingServer() {
            return $this->encoding_server;
        }

		/**
		 * @param \DateTime $created_at
		 * @return AudioEncodingJob
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
         * @return AudioEncodingJob
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
