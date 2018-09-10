<?php

namespace StrimeAPI\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testGetUsers()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/users/get',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'email'} ),
		    'the "email" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'first_name'} ),
		    'the "first_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'last_name'} ),
		    'the "last_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'offer'} ),
		    'the "offer" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'storage_used'} ),
		    'the "storage_used" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'status'} ),
		    'the "status" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'role'} ),
		    'the "role" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'opt_in'} ),
		    'the "opt_in" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'last_login'} ),
		    'the "last_login" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'locale'} ),
		    'the "locale" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }



    public function testGetLastUsers()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/users/get/last/3',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'email'} ),
		    'the "email" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'first_name'} ),
		    'the "first_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'last_name'} ),
		    'the "last_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'offer'} ),
		    'the "offer" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'storage_used'} ),
		    'the "storage_used" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'status'} ),
		    'the "status" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'role'} ),
		    'the "role" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'opt_in'} ),
		    'the "opt_in" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'last_login'} ),
		    'the "last_login" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'locale'} ),
		    'the "locale" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testGetUser()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/user/68c8213a81/get',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'stripe_id'} ),
		    'the "stripe_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'email'} ),
		    'the "email" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'first_name'} ),
		    'the "first_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'last_name'} ),
		    'the "last_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'name'} ),
		    'the "name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'price'} ),
		    'the "price" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'storage_allowed'} ),
		    'the "storage_allowed" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'storage_used'} ),
		    'the "storage_used" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'status'} ),
		    'the "status" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'role'} ),
		    'the "role" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'opt_in'} ),
		    'the "opt_in" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'last_login'} ),
		    'the "last_login" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'locale'} ),
		    'the "locale" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testGetUserByEmail()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/user/romain@digitallift.fr/get-by-email',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'stripe_id'} ),
		    'the "stripe_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'email'} ),
		    'the "email" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'first_name'} ),
		    'the "first_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'last_name'} ),
		    'the "last_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'name'} ),
		    'the "name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'price'} ),
		    'the "price" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'storage_allowed'} ),
		    'the "storage_allowed" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'storage_used'} ),
		    'the "storage_used" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'status'} ),
		    'the "status" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'role'} ),
		    'the "role" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'opt_in'} ),
		    'the "opt_in" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'last_login'} ),
		    'the "last_login" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'locale'} ),
		    'the "locale" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testGetUserByStripeID()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/user/123abc/get-by-stripe-id',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'stripe_id'} ),
		    'the "stripe_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'email'} ),
		    'the "email" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'first_name'} ),
		    'the "first_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'last_name'} ),
		    'the "last_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'name'} ),
		    'the "name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'price'} ),
		    'the "price" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offer'}->{'storage_allowed'} ),
		    'the "storage_allowed" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'storage_used'} ),
		    'the "storage_used" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'status'} ),
		    'the "status" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'role'} ),
		    'the "role" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'opt_in'} ),
		    'the "opt_in" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'last_login'} ),
		    'the "last_login" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'locale'} ),
		    'the "locale" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testGetToken()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/user/68c8213a81/get-token',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'token'} ),
		    'the "token" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testAddEditDeleteUser()
    {
    	// Set a secret_id for the offer
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $secret_id = '';
	    for ($i = 0; $i < 8; $i++) {
	        $secret_id .= $characters[rand(0, $charactersLength - 1)];
	    }

    	$new_user = array(
    		"secret_id" => $secret_id,
    		"offer_id" => "ba7ce04669",
    		"email" => "test@test.com",
    		"first_name" => "test",
    		"last_name" => "test",
    		"password" => "test"
            "locale" => "en"
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'POST',
        	'/user/add',
		    $new_user,
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    201,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

		// Get the ID of the newly created offer
		$user_id = $response->{'user_id'};



		// Test the edition of the offer
		// Set the new Data
		// Set a name for the offer
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $stripe_id = '';
	    for ($i = 0; $i < 8; $i++) {
	        $stripe_id .= $characters[rand(0, $charactersLength - 1)];
	    }

    	$new_data = array(
    		"stripe_id" => $stripe_id,
    		"company" => "Foo",
    		"email" => "foo@bar.com",
    		"country" => "FR",
    		"avatar" => "/avatar/123.jpg",
    		"storage_used" => 100,
    		"old_password" => "test",
    		"new_password" => "foo",
    		"new_password_repeat" => "foo",
    		"empty_address" => 1,
            "locale" => "es"
    	);

        $crawler = $client->request(
        	'PUT',
        	'/user/'.$user_id.'/edit',
		    $new_data,
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);


		// Test the login action
    	$login_details = array(
    		"email" => "foo@bar.com",
    		"password" => "foo",
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'POST',
        	'/user/signin',
		    $login_details,
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);



		// Test the deletion of the offer
        $crawler = $client->request(
        	'DELETE',
        	'/user/'.$user_id.'/delete',
		    array(),
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // The HTTP status code must be 200
		$this->assertEquals(
		    204,
		    $client->getResponse()->getStatusCode()
		);
    }


    public function testSearchUser()
    {
    	$search_details = array(
    		"search" => "ro",
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/user/search',
		    $search_details,
		    array(),
		    array(
		        'HTTP_X-Auth-Token' => 'b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956',
		        'CONTENT_TYPE' => 'application/json',
		    )
		);

		$response = $client->getResponse()->getContent();
		$response = json_decode($response);

        // Response must be JSON
        $this->assertTrue(
		    $client->getResponse()->headers->contains(
		        'Content-Type',
		        'application/json'
		    ),
		    'the "Content-Type" header is "application/json"' // optional message shown on failure
		);

        // The HTTP status code must be 200
		$this->assertEquals(
		    200,
		    $client->getResponse()->getStatusCode()
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'user_id'} ),
		    'the "user_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'stripe_id'} ),
		    'the "stripe_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'email'} ),
		    'the "email" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'first_name'} ),
		    'the "first_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'last_name'} ),
		    'the "last_name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'offer'}->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'offer'}->{'name'} ),
		    'the "name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'offer'}->{'price'} ),
		    'the "price" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'status'} ),
		    'the "status" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'role'} ),
		    'the "role" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'opt_in'} ),
		    'the "opt_in" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'last_login'} ),
		    'the "last_login" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'locale'} ),
		    'the "locale" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }
}
