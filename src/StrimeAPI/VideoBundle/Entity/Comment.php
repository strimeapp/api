<?php

    namespace StrimeAPI\VideoBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_comment", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
     */
    class Comment
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
         * @ORM\ManyToOne(targetEntity="StrimeAPI\UserBundle\Entity\Contact")
         */
        protected $contact;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Video")
         */
        protected $video;

        /**
         * @ORM\Column(name="is_global", type="integer", nullable=false)
         */
        protected $is_global = 0;

        /**
         * @ORM\Column(name="comment", type="text", nullable=false)
         */
        protected $comment;

        /**
         * @ORM\Column(name="time", type="float", nullable=true)
         */
        protected $time = 0;

        /**
         * @ORM\Column(name="area", type="text", nullable=true)
         */
        protected $area;

        /**
         * @ORM\Column(name="done", type="integer", nullable=false)
         */
        protected $done = 0;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Comment")
         */
        protected $answer_to;

        /**
        * @ORM\Column(name="s3_url", type="string", length=255, nullable=true)
        */
        private $s3_url;

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
         * @return Comment
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
         * @return Comment
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
         * @param integer $contact
         * @return Comment
         */
        public function setContact($contact) {
            $this->contact = $contact;
            return $this;
        }

        /**
         * @return integer
         */
        public function getContact() {
            return $this->contact;
        }

        /**
         * @param integer $video
         * @return Comment
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
         * @param integer $is_global
         * @return Comment
         */
        public function setIsGlobal($is_global) {
            $this->is_global = $is_global;
            return $this;
        }

        /**
         * @return integer
         */
        public function getIsGlobal() {
            return $this->is_global;
        }

        /**
         * @param text $comment
         * @return Comment
         */
        public function setComment($comment) {
            $this->comment = $comment;
            return $this;
        }

        /**
         * @return text
         */
        public function getComment() {
            return $this->comment;
        }

        /**
         * @param integer $time
         * @return Comment
         */
        public function setTime($time) {
            $this->time = $time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTime() {
            return $this->time;
        }

        /**
         * @param text $area
         * @return Comment
         */
        public function setArea($area) {
            $this->area = $area;
            return $this;
        }

        /**
         * @return text
         */
        public function getArea() {
            return $this->area;
        }

        /**
         * @param integer $done
         * @return Comment
         */
        public function setDone($done) {
            $this->done = $done;
            return $this;
        }

        /**
         * @return integer
         */
        public function getDone() {
            return $this->done;
        }

        /**
         * @param integer $answer_to
         * @return Comment
         */
        public function setAnswerTo($answer_to) {
            $this->answer_to = $answer_to;
            return $this;
        }

        /**
         * @return integer
         */
        public function getAnswerTo() {
            return $this->answer_to;
        }

        /**
        * @param string $s3_url
        * @return Comment
        */
        public function setS3Url($s3_url) {
            $this->s3_url = $s3_url;
            return $this;
        }

        /**
        * @return string
        */
        public function getS3Url() {
            return $this->s3_url;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Comment
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
         * @return Comment
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
