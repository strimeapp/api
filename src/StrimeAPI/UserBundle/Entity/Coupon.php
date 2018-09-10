<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_coupon")
     */
    class Coupon
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="stripe_id", type="string", length=255, nullable=false)
         */
        protected $stripe_id;

        /**
         * @ORM\ManyToMany(targetEntity="StrimeAPI\UserBundle\Entity\Offer", cascade={"persist"})
         */
        protected $offers;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;

        /**
         * @ORM\Column(name="updated_at", type="datetime")
         */
        protected $updated_at;

        


        public function __construct() {
            $this->offers = new ArrayCollection();
        }



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param string $stripe_id
         * @return Coupon
         */
        public function setStripeId($stripe_id) {
            $this->stripe_id = $stripe_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getStripeId() {
            return $this->stripe_id;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Offer $offer
         * @return Coupon
         */
        public function addOffer(\StrimeAPI\UserBundle\Entity\Offer $offer) {
            $this->offers[] = $offer;
            return $this;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Offer $offer
         */
        public function removeOffer(\StrimeAPI\UserBundle\Entity\Offer $offer) {
            $this->offers->removeElement($offer);
        }

        /**
         * @return Coupon
         */
        public function getOffers() {
            return $this->offers;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Offer
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
		 * @return Offer
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