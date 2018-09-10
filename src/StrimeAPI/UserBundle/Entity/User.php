<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_user")
     */
    class User
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
         * @ORM\Column(name="stripe_id", type="string", length=255, nullable=true)
         */
        protected $stripe_id;

        /**
         * @ORM\Column(name="stripe_sub_id", type="string", length=255, nullable=true)
         */
        protected $stripe_sub_id;

        /**
         * @ORM\Column(name="email", type="string", length=200, nullable=false)
         */
        protected $email;

        /**
         * @ORM\Column(name="password", type="string", length=200, nullable=false)
         */
        protected $password;

        /**
         * @ORM\Column(name="password_time", type="integer", nullable=false)
         */
        protected $password_time;

        /**
         * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
         */
        protected $first_name;

        /**
         * @ORM\Column(name="last_name", type="string", length=100, nullable=true)
         */
        protected $last_name;

        /**
         * @ORM\Column(name="company", type="string", length=100, nullable=true)
         */
        protected $company;

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
         * @ORM\Column(name="vat_number", type="string", length=20, nullable=true)
         */
        protected $vat_number;

        /**
         * @ORM\ManyToOne(targetEntity="Offer")
         */
        protected $offer;

        /**
         * @ORM\ManyToMany(targetEntity="StrimeAPI\UserBundle\Entity\Right", cascade={"persist"})
         */
        protected $rights;

        /**
         * @ORM\ManyToMany(targetEntity="StrimeAPI\UserBundle\Entity\Coupon", cascade={"persist"})
         */
        protected $coupons;

        /**
         * @ORM\Column(name="storage_used", type="float", nullable=false)
         */
        protected $storage_used = 0;

        /**
         * @ORM\Column(name="status", type="string", length=15, nullable=false)
         */
        protected $status = "active";

        /**
         * @ORM\Column(name="role", type="string", length=20, nullable=false)
         */
        protected $role = "user";

        /**
         * @ORM\Column(name="opt_in", type="integer", nullable=false)
         */
        protected $opt_in = 0;

        /**
         * @ORM\Column(name="mail_notification", type="string", length=20, nullable=false)
         */
        protected $mail_notification = "now";

        /**
         * @ORM\Column(name="nb_contacts", type="integer", nullable=false)
         */
        protected $nb_contacts = 0;

        /**
        * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
        */
        private $avatar;

        /**
         * @ORM\Column(name="last_login", type="integer", nullable=false)
         */
        protected $last_login = 0;

        /**
        * @ORM\Column(name="locale", type="string", length=2, nullable=true)
        */
        private $locale;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;

        /**
         * @ORM\Column(name="updated_at", type="datetime")
         */
        protected $updated_at;




        public function __construct() {
            $this->rights = new ArrayCollection();
            $this->coupons = new ArrayCollection();
        }



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param string $secret_id
         * @return User
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
         * @param string $stripe_id
         * @return User
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
         * @param string $stripe_sub_id
         * @return User
         */
        public function setStripeSubId($stripe_sub_id) {
            $this->stripe_sub_id = $stripe_sub_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getStripeSubId() {
            return $this->stripe_sub_id;
        }

        /**
         * @param string $email
         * @return User
         */
        public function setEmail($email) {
            $this->email = $email;
            return $this;
        }

        /**
         * @return string
         */
        public function getEmail() {
            return $this->email;
        }

        /**
         * @param string $password
         * @return User
         */
        public function setPassword($password) {
            $this->password = $password;
            return $this;
        }

        /**
         * @return string
         */
        public function getPassword() {
            return $this->password;
        }

        /**
         * @param integer $password_time
         * @return User
         */
        public function setPasswordTime($password_time) {
            $this->password_time = $password_time;
            return $this;
        }

        /**
         * @return integer
         */
        public function getPasswordTime() {
            return $this->password_time;
        }

        /**
         * @param string $first_name
         * @return User
         */
        public function setFirstName($first_name) {
            $this->first_name = $first_name;
            return $this;
        }

        /**
         * @return string
         */
        public function getFirstName() {
            return $this->first_name;
        }

        /**
         * @param string $last_name
         * @return User
         */
        public function setLastName($last_name) {
            $this->last_name = $last_name;
            return $this;
        }

        /**
         * @return string
         */
        public function getLastName() {
            return $this->last_name;
        }

        /**
         * @param string $company
         * @return User
         */
        public function setCompany($company) {
            $this->company = $company;
            return $this;
        }

        /**
         * @return string
         */
        public function getCompany() {
            return $this->company;
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
         * @param string $vat_number
         * @return User
         */
        public function setVatNumber($vat_number) {
            $this->vat_number = $vat_number;
            return $this;
        }

        /**
         * @return string
         */
        public function getVatNumber() {
            return $this->vat_number;
        }

        /**
         * @param integer $offer
         * @return User
         */
        public function setOffer($offer) {
            $this->offer = $offer;
            return $this;
        }

        /**
         * @return integer
         */
        public function getOffer() {
            return $this->offer;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Right $right
         * @return User
         */
        public function addRight(\StrimeAPI\UserBundle\Entity\Right $right) {
            $this->rights[] = $right;
            return $this;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Right $right
         */
        public function removeRight(\StrimeAPI\UserBundle\Entity\Right $right) {
            $this->rights->removeElement($right);
        }

        /**
         * @return Right
         */
        public function getRights() {
            return $this->rights;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Coupon $coupon
         * @return User
         */
        public function addCoupon(\StrimeAPI\UserBundle\Entity\Coupon $coupon) {
            $this->coupons[] = $coupon;
            return $this;
        }

        /**
         * @param \StrimeAPI\UserBundle\Entity\Coupon $coupon
         */
        public function removeCoupon(\StrimeAPI\UserBundle\Entity\Coupon $coupon) {
            $this->coupons->removeElement($coupon);
        }

        /**
         * @return User
         */
        public function getCoupons() {
            return $this->coupons;
        }

        /**
         * @param float $storage_used
         * @return User
         */
        public function setStorageUsed($storage_used) {
            $this->storage_used = $storage_used;
            return $this;
        }

        /**
         * @return float
         */
        public function getStorageUsed() {
            return $this->storage_used;
        }

        /**
         * @param string $status
         * @return User
         */
        public function setStatus($status) {
            $this->status = $status;
            return $this;
        }

        /**
         * @return string
         */
        public function getStatus() {
            return $this->status;
        }

        /**
         * @param string $role
         * @return User
         */
        public function setRole($role) {
            $this->role = $role;
            return $this;
        }

        /**
         * @return string
         */
        public function getRole() {
            return $this->role;
        }

        /**
         * @param integer $opt_in
         * @return User
         */
        public function setOptIn($opt_in) {
            $this->opt_in = $opt_in;
            return $this;
        }

        /**
         * @return integer
         */
        public function getOptIn() {
            return $this->opt_in;
        }

        /**
         * @param string $mail_notification
         * @return User
         */
        public function setMailNotification($mail_notification) {
            $this->mail_notification = $mail_notification;
            return $this;
        }

        /**
         * @return string
         */
        public function getMailNotification() {
            return $this->mail_notification;
        }

        /**
         * @param integer $nb_contacts
         * @return User
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
         * @param string $avatar
         * @return User
         */
        public function setAvatar($avatar) {
            $this->avatar = $avatar;
            return $this;
        }

        /**
         * @return string
         */
        public function getAvatar() {
            return $this->avatar;
        }

        /**
         * @param string $locale
         * @return User
         */
        public function setLocale($locale) {
            $this->locale = $locale;
            return $this;
        }

        /**
         * @return string
         */
        public function getLocale() {
            return $this->locale;
        }

        /**
         * @param integer $last_login
         * @return User
         */
        public function setLastLogin($last_login) {
            $this->last_login = $last_login;
            return $this;
        }

        /**
         * @return integer
         */
        public function getLastLogin() {
            return $this->last_login;
        }

		/**
		 * @param \DateTime $created_at
		 * @return User
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
		 * @return User
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

        /**
         * Now we tell doctrine that before we persist or update, we create the needed folders.
         *
         * @ORM\PrePersist
         * @ORM\PreUpdate
         */
        public function createFolders() {

            $directory_invoices = __DIR__.'/../../../../web/invoices/'.$this->getSecretId();
            $directory_videos = __DIR__.'/../../../../web/uploads/videos/'.$this->getSecretId();

            if(!file_exists($directory_invoices))
                mkdir($directory_invoices, 0755, TRUE);

            if(!file_exists($directory_videos))
                mkdir($directory_videos, 0755, TRUE);
        }





	    /**
	     * @return string
	     */
	    public function getFullName() {
	    	return sprintf('%s %s', $this->first_name, $this->last_name);
		}
    }
