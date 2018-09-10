<?php

namespace StrimeAPI\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CouponControllerTest extends WebTestCase
{
    public function testGetCoupons()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/coupons/get',
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
		    isset( $response->{'results'}[0]->{'stripe_id'} ),
		    'the "stripe_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}[0]->{'offers'} ),
		    'the "offers" data is part of the response' // optional message shown on failure
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


    public function testGetCoupon()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/coupon/TEST/get',
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
		    isset( $response->{'results'}->{'stripe_id'} ),
		    'the "stripe_id" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'offers'} ),
		    'the "offers" data is part of the response' // optional message shown on failure
		);

        // Response must be JSON
        $this->assertTrue(
		    isset( $response->{'results'}->{'updated_at'} ),
		    'the "updated_at" data is part of the response' // optional message shown on failure
		);
    }


    public function testAddEditDeleteCoupon()
    {
    	// Set a stripe_id for the coupon
    	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $stripe_id = '';
	    for ($i = 0; $i < 8; $i++) {
	        $stripe_id .= $characters[rand(0, $charactersLength - 1)];
	    }

    	$new_coupon = array(
    		"stripe_id" => $stripe_id,
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'POST',
        	'/coupon/add',
		    $new_coupon,
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
		    isset( $response->{'stripe_id'} ),
		    'the "offer_id" data is part of the response' // optional message shown on failure
		);

		// Get the ID of the newly created offer
		$stripe_id = $response->{'stripe_id'};



		// Test the edition of the offer
		// Set the new Data

    	$new_data = array(
    		"offers" => array("ba7ce04669","937dacf8b2")
    	);

        $crawler = $client->request(
        	'PUT',
        	'/coupon/'.$stripe_id.'/edit',
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
        	'/coupon/'.$stripe_id.'/delete',
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