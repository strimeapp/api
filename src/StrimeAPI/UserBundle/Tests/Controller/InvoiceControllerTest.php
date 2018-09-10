<?php

namespace StrimeAPI\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InvoiceControllerTest extends WebTestCase
{
    public function testAddInvoice()
    {
    	$new_invoice = array(
    		"secret_id" => date('Ymd')."00001",
    		"stripe_id" => "abc123",
    		"user_id" => "68c8213a81",
    		"total_amount" => 15.0,
    		"amount_wo_taxes" => 15.0,
    		"taxes" => 0,
    		"tax_rate" => 0,
    		"day" => date('d'),
    		"month" => date('m'),
    		"year" => date('Y'),
    		"plan_start_date" => date('d/m/Y'),
    		"plan_end_date" => date('d/m/Y')
    	);

        $client = static::createClient();
        $crawler = $client->request(
        	'POST',
        	'/invoice/add',
		    $new_invoice,
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
		    isset( $response->{'invoice_id'} ),
		    'the "invoice_id" data is part of the response' // optional message shown on failure
		);
    }


    public function testGetInvoices()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/invoices/get',
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

		if(is_array($response->{'results'}) && isset($response->{'results'}[0])) {
	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'invoice_id'} ),
			    'the "invoice_id" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'total_amount'} ),
			    'the "total_amount" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'amount_wo_taxes'} ),
			    'the "amount_wo_taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'taxes'} ),
			    'the "taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'tax_rate'} ),
			    'the "tax_rate" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'day'} ),
			    'the "day" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'month'} ),
			    'the "month" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'year'} ),
			    'the "year" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'plan_start_date'} ),
			    'the "plan_start_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'plan_end_date'} ),
			    'the "plan_end_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'created_at'} ),
			    'the "created_at" data is part of the response' // optional message shown on failure
			);
		}
    }



    public function testGetInvoicesByUsers()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/invoices/68c8213a81/get',
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

		if(is_array($response->{'results'}) && isset($response->{'results'}[0])) {
	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'invoice_id'} ),
			    'the "invoice_id" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'total_amount'} ),
			    'the "total_amount" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'amount_wo_taxes'} ),
			    'the "amount_wo_taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'taxes'} ),
			    'the "taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'tax_rate'} ),
			    'the "tax_rate" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'day'} ),
			    'the "day" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'month'} ),
			    'the "month" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'year'} ),
			    'the "year" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'plan_start_date'} ),
			    'the "plan_start_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'plan_end_date'} ),
			    'the "plan_end_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'user'}->{'user_id'} ),
			    'the "user:user_id" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'user'}->{'first_name'} ),
			    'the "user:first_name" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'user'}->{'last_name'} ),
			    'the "user:last_name" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'user'}->{'email'} ),
			    'the "user:email" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'created_at'} ),
			    'the "created_at" data is part of the response' // optional message shown on failure
			);
		}
    }



    public function testGetInvoicesOnPeriod()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/invoices/get/period/start/0/stop/'.time(),
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

		if(is_array($response->{'results'}) && isset($response->{'results'}[0])) {
	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'invoice_id'} ),
			    'the "invoice_id" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'total_amount'} ),
			    'the "total_amount" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'amount_wo_taxes'} ),
			    'the "amount_wo_taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'taxes'} ),
			    'the "taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'tax_rate'} ),
			    'the "tax_rate" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'day'} ),
			    'the "day" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'month'} ),
			    'the "month" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'year'} ),
			    'the "year" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'plan_start_date'} ),
			    'the "plan_start_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'plan_end_date'} ),
			    'the "plan_end_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}[0]->{'created_at'} ),
			    'the "created_at" data is part of the response' // optional message shown on failure
			);
		}
    }


    public function testGetInvoice()
    {
        $client = static::createClient();
        $crawler = $client->request(
        	'GET',
        	'/invoice/'.date('Ymd').'00001'.'/get',
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

		if(is_array($response->{'results'}) && isset($response->{'results'}[0])) {
	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'invoice_id'} ),
			    'the "invoice_id" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'total_amount'} ),
			    'the "total_amount" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'amount_wo_taxes'} ),
			    'the "amount_wo_taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'taxes'} ),
			    'the "taxes" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'tax_rate'} ),
			    'the "tax_rate" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'day'} ),
			    'the "day" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'month'} ),
			    'the "month" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'year'} ),
			    'the "year" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'plan_start_date'} ),
			    'the "plan_start_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'plan_end_date'} ),
			    'the "plan_end_date" data is part of the response' // optional message shown on failure
			);

	        // Response must be JSON
	        $this->assertTrue(
			    isset( $response->{'results'}->{'created_at'} ),
			    'the "created_at" data is part of the response' // optional message shown on failure
			);
		}
    }



    public function testEditDeleteInvoice()
    {

		// Get the ID of the newly created invoice
		$invoice_id = date('Ymd')."00001";

		// Test the edition of the invoice
		// Set the new Data

    	$new_data = array(
    		"stripe_id" => 'foo345',
    		"plan_end_date" => date('d/m/Y', strtotime('+1 month')),
    	);

    	$client = static::createClient();
        $crawler = $client->request(
        	'PUT',
        	'/invoice/'.$invoice_id.'/edit',
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
        	'/invoice/'.$invoice_id.'/delete',
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