<?php

    namespace StrimeAPI\VideoBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_comment_with_thumb_in_error")
     */
    class CommentWithThumbInError
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\VideoBundle\Entity\Comment")
         */
        protected $comment;

        /**
         * @ORM\Column(name="nb_attempts", type="integer", nullable=false)
         */
        protected $nb_attempts = 0;

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
         * @param integer $comment
         * @return CommentWithThumbInError
         */
        public function setComment($comment) {
            $this->comment = $comment;
            return $this;
        }

        /**
         * @return integer
         */
        public function getComment() {
            return $this->comment;
        }

        /**
         * @param integer $nb_attempts
         * @return CommentWithThumbInError
         */
        public function setNbAttempts($nb_attempts) {
            $this->nb_attempts = $nb_attempts;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNbAttempts() {
            return $this->nb_attempts;
        }

		/**
		 * @param \DateTime $created_at
		 * @return CommentWithThumbInError
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
         * @return CommentWithThumbInError
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
