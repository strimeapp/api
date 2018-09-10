<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="api_email_to_confirm")
     */
    class EmailToConfirm
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\UserBundle\Entity\User")
         */
        protected $user;

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
         * @param integer $user
         * @return EmailToConfirm
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
         * @param \DateTime $created_at
         * @return EmailToConfirm
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
         */
        public function updatedTimestamps() {

            if($this->getCreatedAt() == null)
            {
                $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            }
        }
    }