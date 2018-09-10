<?php

namespace StrimeAPI\GlobalBundle\Tests\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use StrimeAPI\GlobalBundle\Auth\HeadersAuthorization;

class HeadearsAuthorizationTest extends WebTestCase
{
    public function testGetToken()
    {
    	$client = static::createClient();
        $crawler = $client->request('GET', '/api/1.0/users/get');
        $headers = $client->getResponse()->headers;
        $headers->set('X-Auth-Token', 'abc123');

        $auth = new HeadersAuthorization();
        $tokens = $auth->getToken($headers);

        $this->assertTrue(
        	strlen($tokens) > 0,
        	'We should get a valid token from this request.'
        );
    }
}