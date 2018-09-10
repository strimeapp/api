<?php

namespace StrimeAPI\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OfferControllerTest extends WebTestCase
{
    public function testGetOffers()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/offers/get',
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
		    isset( $response->{'results'}[0]->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'name'} ),
		    'the "name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'price'} ),
		    'the "price" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'storage_allowed'} ),
		    'the "storage_allowed" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'nb_videos'} ),
		    'the "nb_videos" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testGetOffer()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/offer/ba7ce04669/get',
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
		    isset( $response->{'results'}->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'name'} ),
		    'the "name" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'price'} ),
		    'the "price" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'storage_allowed'} ),
		    'the "storage_allowed" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'nb_videos'} ),
		    'the "nb_videos" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testAddEditDeleteOffer()
    {
    	// Set a name for the offer
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $name = '';
	    for ($i = 0; $i < 8; $i++) {
	        $name .= $characters[rand(0, $charactersLength - 1)];
	    }

    	$new_offer = array(
    		"name" => $name,
    		"price" => 123,
    		"nb_videos" => 123,
    		"storage_allowed" => 123
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'POST',
        	'/offer/add',
		    $new_offer,
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
		    isset( $response->{'offer_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

		// Get the ID of the newly created offer
		$offer_id = $response->{'offer_id'};



		// Test the edition of the offer
		// Set the new Data
		// Set a name for the offer
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $name = '';
	    for ($i = 0; $i < 8; $i++) {
	        $name .= $characters[rand(0, $charactersLength - 1)];
	    }

    	$new_data = array(
    		"name" => $name,
    		"price" => 456,
    		"nb_videos" => 0,
    		"storage_allowed" => 456
    	);

        $crawler = $client->request(
        	'PUT',
        	'/offer/'.$offer_id.'/edit',
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



		// Test the deletion of the offer
        $crawler = $client->request(
        	'DELETE',
        	'/offer/'.$offer_id.'/delete',
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
}