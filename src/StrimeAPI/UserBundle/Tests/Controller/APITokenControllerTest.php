<?php

namespace StrimeAPI\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class APITokenControllerTest extends WebTestCase
{
    public function testGetToken()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/token/b9030ce5a6a63aa9afab0ca69dbc7349fce8c1e46c3795d956/get',
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
		    isset( $response->{'results'}->{'user'}->{'user_id'} ),
		    'the "user ID" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'user'}->{'first_name'} ),
		    'the "user firstname" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'user'}->{'last_name'} ),
		    'the "user lastname" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'created_at'} ),
		    'the "created_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testAddEditDeleteToken()
    {
    	// Set a token
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $token = '';
	    for ($i = 0; $i < 8; $i++) {
	        $token .= $characters[rand(0, $charactersLength - 1)];
	    }

    	$new_token = array(
    		"token" => $token,
    		"user_id" => 1
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'POST',
        	'/token/add',
		    $new_token,
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
		$this->assertGreaterThan(
		    199,
		    $client->getResponse()->getStatusCode()
		);

        // The HTTP status code must be 200
		$this->assertLessThan(
		    202,
		    $client->getResponse()->getStatusCode()
		);
    }
}