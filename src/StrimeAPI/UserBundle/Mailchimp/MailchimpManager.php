<?php

namespace StrimeAPI\UserBundle\Mailchimp;

class MailchimpManager {

    protected $username = "Romain";
    protected $api_key = "d98f68a896effbee74761c6345736b2b-us11";
    protected $endpoint = "https://us11.api.mailchimp.com/3.0/";
    public $list;
	public $email;
    public $old_email;
    public $first_name;
    public $last_name;
    public $locale;
    public $status;
    public $active;
    public $member_id;

	public function __contruct() {

        $this->username = $username;
        $this->api_key = $api_key;
        $this->endpoint = $endpoint;
		$this->list = $list;
        $this->email = $email;
        $this->old_email = $old_email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->locale = strtoupper($locale);
        $this->status = $status;
        $this->active = $active;
        $this->member_id = $member_id;
	}



	/**
     * @return string JSON returned by Mailchimp
     */
    public function getLists()
    {

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $this->endpoint."lists", [
            'auth' => [$this->username, $this->api_key],
            'http_errors' => false,
            'timeout' => 30,
        ]);

        $curl_status = $json_response->getStatusCode();
        $return = $json_response->getBody();

        return $return;
    }



    /**
     * @return string JSON returned by Mailchimp
     */
    public function subscribeMember()
    {

        $data = array(
            "email_address" => $this->email,
            "status" => "subscribed",
            "email_type" => "html",
            "merge_fields" => array(
                "ACTIVE" => "active"
            )
        );

        if($this->first_name != NULL)
            $data["merge_fields"]["FNAME"] = $this->first_name;
        if($this->last_name != NULL)
            $data["merge_fields"]["LNAME"] = $this->last_name;
        if($this->locale != NULL)
            $data["merge_fields"]["LOCALE"] = $this->locale;

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('POST', $this->endpoint . "lists/".$this->list."/members", [
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'http_errors' => false,
            'auth' => [$this->username, $this->api_key],
            'json' => $data,
            'timeout' => 30
        ]);

        $curl_status = $json_response->getStatusCode();
        $return = $json_response->getBody();

        return $return;
    }



    /**
     * @return string JSON returned by Mailchimp
     */
    public function getMember()
    {

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('GET', $this->endpoint . "lists/".$this->list."/members/".md5($this->email), [
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'http_errors' => false,
            'auth' => [$this->username, $this->api_key],
            'timeout' => 30
        ]);

        $curl_status = $json_response->getStatusCode();
        $return = $json_response->getBody();

        return $return;
    }



    /**
     * @return string JSON returned by Mailchimp
     */
    public function unsubscribeMember()
    {

        $data = array(
            "email_address" => $this->email,
            "status" => "unsubscribed"
        );

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('PATCH', $this->endpoint . "lists/".$this->list."/members/".md5($this->email), [
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'http_errors' => false,
            'auth' => [$this->username, $this->api_key],
            'json' => $data,
            'timeout' => 30
        ]);

        $curl_status = $json_response->getStatusCode();
        $return = $json_response->getBody();

        return $return;
    }



    /**
     * @return string JSON returned by Mailchimp
     */
    public function editMember()
    {

        $data = array();

        if($this->email != NULL)
            $data["email_address"] = $this->email;

        if($this->status != NULL)
            $data["status"] = $this->status;

        if($this->first_name != NULL)
            $data["merge_fields"]["FNAME"] = $this->first_name;

        if($this->last_name != NULL)
            $data["merge_fields"]["LNAME"] = $this->last_name;

        if($this->locale != NULL)
            $data["merge_fields"]["LOCALE"] = $this->locale;

        if($this->active != NULL)
            $data["merge_fields"]["ACTIVE"] = $this->active;

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('PATCH', $this->endpoint . "lists/".$this->list."/members/".md5($this->old_email), [
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'http_errors' => false,
            'auth' => [$this->username, $this->api_key],
            'json' => $data,
            'timeout' => 30
        ]);

        $curl_status = $json_response->getStatusCode();
        $return = $json_response->getBody();

        return $return;
    }



    /**
     * @return string JSON returned by Mailchimp
     */
    public function deleteMember()
    {

        $client = new \GuzzleHttp\Client();
        $json_response = $client->request('DELETE', $this->endpoint . "lists/".$this->list."/members/".md5($this->email), [
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'http_errors' => false,
            'auth' => [$this->username, $this->api_key],
            'timeout' => 30
        ]);

        $curl_status = $json_response->getStatusCode();
        $return = $json_response->getBody();

        return $return;
    }
}
