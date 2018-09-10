<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_user_address")
     */
    class Address
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
         * @ORM\Column(name="address", type="string", length=100, nullable=true)
         */
        protected $address;

        /**
         * @ORM\Column(name="address_more", type="string", length=100, nullable=true)
         */
        protected $address_more;

        /**
         * @ORM\Column(name="zip", type="string", length=10, nullable=true)
         */
        protected $zip;

        /**
         * @ORM\Column(name="city", type="string", length=100, nullable=true)
         */
        protected $city;

        /**
         * @ORM\Column(name="state", type="string", length=50, nullable=true)
         */
        protected $state;

        /**
         * @ORM\Column(name="country", type="string", length=50, nullable=true)
         */
        protected $country;

        /**
         * @ORM\Column(name="latitude", type="float", nullable=true)
         */
        protected $latitude;

        /**
         * @ORM\Column(name="longitude", type="float", nullable=true)
         */
        protected $longitude;

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
         * @param integer $user
         * @return Address
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
         * @param string $address
         * @return User
         */
        public function setAddress($address) {
            $this->address = $address;
            return $this;
        }

        /**
         * @return string
         */
        public function getAddress() {
            return $this->address;
        }

        /**
         * @param string $address_more
         * @return User
         */
        public function setAddressMore($address_more) {
            $this->address_more = $address_more;
            return $this;
        }

        /**
         * @return string
         */
        public function getAddressMore() {
            return $this->address_more;
        }

        /**
         * @param string $zip
         * @return User
         */
        public function setZip($zip) {
            $this->zip = $zip;
            return $this;
        }

        /**
         * @return string
         */
        public function getZip() {
            return $this->zip;
        }

        /**
         * @param string $city
         * @return User
         */
        public function setCity($city) {
            $this->city = $city;
            return $this;
        }

        /**
         * @return string
         */
        public function getCity() {
            return $this->city;
        }

        /**
         * @param string $state
         * @return User
         */
        public function setState($state) {
            $this->state = $state;
            return $this;
        }

        /**
         * @return string
         */
        public function getState() {
            return $this->state;
        }

        /**
         * @param string $country
         * @return User
         */
        public function setCountry($country) {
            $this->country = $country;
            return $this;
        }

        /**
         * @return string
         */
        public function getCountry() {
            return $this->country;
        }

        /**
         * @param float $latitude
         * @return Address
         */
        public function setLatitude($latitude) {
            $this->latitude = $latitude;
            return $this;
        }

        /**
         * @return string
         */
        public function getLatitude() {
            return $this->latitude;
        }

        /**
         * @param float $longitude
         * @return Address
         */
        public function setLongitude($longitude) {
            $this->longitude = $longitude;
            return $this;
        }

        /**
         * @return float
         */
        public function getLongitude() {
            return $this->longitude;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Address
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
         * @return Address
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