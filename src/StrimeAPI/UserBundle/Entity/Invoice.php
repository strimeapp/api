<?php

    namespace StrimeAPI\UserBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="api_invoice")
     */
    class Invoice
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="invoice_id", type="string", length=20, nullable=false)
         */
        protected $invoice_id;

        /**
         * @ORM\Column(name="stripe_id", type="string", length=20, nullable=true)
         */
        protected $stripe_id;

        /**
         * @ORM\ManyToOne(targetEntity="StrimeAPI\UserBundle\Entity\User")
         */
        protected $user;

        /**
         * @ORM\Column(name="total_amount", type="float", nullable=false)
         */
        protected $total_amount;

        /**
         * @ORM\Column(name="amount_wo_taxes", type="float", nullable=false)
         */
        protected $amount_wo_taxes;

        /**
         * @ORM\Column(name="taxes", type="float", nullable=false)
         */
        protected $taxes;

        /**
         * @ORM\Column(name="tax_rate", type="integer", nullable=true)
         */
        protected $tax_rate;

        /**
         * @ORM\Column(name="day", type="string", length=2, nullable=false)
         */
        protected $day;

        /**
         * @ORM\Column(name="month", type="string", length=2, nullable=false)
         */
        protected $month;

        /**
         * @ORM\Column(name="year", type="string", length=4, nullable=false)
         */
        protected $year;

        /**
         * @ORM\Column(name="plan_start_date", type="string", length=10, nullable=true)
         */
        protected $plan_start_date;

        /**
         * @ORM\Column(name="plan_end_date", type="string", length=10, nullable=true)
         */
        protected $plan_end_date;

        /**
         * @ORM\Column(name="status", type="integer", nullable=false)
         */
        protected $status = 0;

        /**
         * @ORM\Column(name="deleted_user_id", type="string", length=20, nullable=true)
         */
        protected $deleted_user_id;

        /**
         * @ORM\Column(name="user_name", type="string", length=100, nullable=true)
         */
        protected $user_name;

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
         * @param string $invoice_id
         * @return Invoice
         */
        public function setInvoiceId($invoice_id) {
            $this->invoice_id = $invoice_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getInvoiceId() {
            return $this->invoice_id;
        }

        /**
         * @param string $stripe_id
         * @return Invoice
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
         * @param integer $user
         * @return Invoice
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
         * @param float $total_amount
         * @return Invoice
         */
        public function setTotalAmount($total_amount) {
            $this->total_amount = $total_amount;
            return $this;
        }

        /**
         * @return float
         */
        public function getTotalAmount() {
            return $this->total_amount;
        }

        /**
         * @param float $amount_wo_taxes
         * @return Invoice
         */
        public function setAmountWoTaxes($amount_wo_taxes) {
            $this->amount_wo_taxes = $amount_wo_taxes;
            return $this;
        }

        /**
         * @return string
         */
        public function getAmountWoTaxes() {
            return $this->amount_wo_taxes;
        }

        /**
         * @param float $taxes
         * @return Invoice
         */
        public function setTaxes($taxes) {
            $this->taxes = $taxes;
            return $this;
        }

        /**
         * @return float
         */
        public function getTaxes() {
            return $this->taxes;
        }

        /**
         * @param integer $tax_rate
         * @return Invoice
         */
        public function setTaxRate($tax_rate) {
            $this->tax_rate = $tax_rate;
            return $this;
        }

        /**
         * @return integer
         */
        public function getTaxRate() {
            return $this->tax_rate;
        }

        /**
         * @param string $day
         * @return Invoice
         */
        public function setDay($day) {
            $this->day = $day;
            return $this;
        }

        /**
         * @return string
         */
        public function getDay() {
            return $this->day;
        }

        /**
         * @param string $month
         * @return Invoice
         */
        public function setMonth($month) {
            $this->month = $month;
            return $this;
        }

        /**
         * @return string
         */
        public function getMonth() {
            return $this->month;
        }

        /**
         * @param string $year
         * @return Invoice
         */
        public function setYear($year) {
            $this->year = $year;
            return $this;
        }

        /**
         * @return string
         */
        public function getYear() {
            return $this->year;
        }

        /**
         * @param string $plan_start_date
         * @return Invoice
         */
        public function setPlanStartDate($plan_start_date) {
            $this->plan_start_date = $plan_start_date;
            return $this;
        }

        /**
         * @return string
         */
        public function getPlanStartDate() {
            return $this->plan_start_date;
        }

        /**
         * @param string $plan_start_date
         * @return Invoice
         */
        public function setPlanEndDate($plan_end_date) {
            $this->plan_end_date = $plan_end_date;
            return $this;
        }

        /**
         * @return string
         */
        public function getPlanEndDate() {
            return $this->plan_end_date;
        }

        /**
         * @param integer $status
         * @return Invoice
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
         * @param string $deleted_user_id
         * @return Invoice
         */
        public function setDeletedUserId($deleted_user_id) {
            $this->deleted_user_id = $deleted_user_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getDeletedUserId() {
            return $this->deleted_user_id;
        }

        /**
         * @param string $user_name
         * @return Invoice
         */
        public function setUserName($user_name) {
            $this->user_name = $user_name;
            return $this;
        }

        /**
         * @return string
         */
        public function getUserName() {
            return $this->user_name;
        }

		/**
		 * @param \DateTime $created_at
		 * @return Invoice
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
		 * @return Invoice
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