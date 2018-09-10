<?php

namespace StrimeAPI\VideoBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

class LoadBalancing {

    private $container;
    protected $em;



    public function __construct(EntityManager $em, Container $container) {
        $this->em = $em;
        $this->container = $container;
    }



    /**
     * @return null
     */
    public function getEncodingServer()
    {
        // Get the list of encoding servers
        $encoding_servers_list = $this->container->getParameter('strime_encoding_servers_list');
        $servers_list = explode(",", $encoding_servers_list);

        // Prepare the results
        $load_results = array();

        // Loop through the servers
        foreach ($servers_list as $server) {

            // Send a request to the server to get the load
            $client = new \GuzzleHttp\Client();
            $json_response = $client->request('GET', "http://".$server.'/loadinfo/5', [
                'http_errors' => false,
            ]);

            $curl_status = $json_response->getStatusCode();
            $response = json_decode( $json_response->getBody()->getContents() );

            // If the cURL request failed, save a high value because we don't want to use this server.
            if($curl_status != 200) {
                $load_results[ $server ] = 1000;
            }

            // Else, if the cURL request succeeded, save the load value.
            else {
                $load_results[ $server ] = $response->{'load'};
            }
        }

        // Check the environment to define which server to use
        switch ( $this->container->getParameter("kernel.environment") ) {
            case 'prod':
                $min_load = 2000;

                // Loop through the servers to get the server with the lowest load
                foreach ($load_results as $server => $load) {

                    // Compare the load to the actual minimum load
                    if($load < $min_load) {
                        $min_load = $load;
                        $server_to_use = "https://".$server.'/';
                    }

                    // Check that the min_load is different from 1000, which would mean that all the cURL requests have failed.
                    if($min_load == 2000) {
                        $server_to_use = FALSE;
                    }
                }
                break;

            case 'test':
                $server_to_use = 'https://encoding-dev.strime.io/';
                break;

            case 'dev':
                $server_to_use = 'http://localhost:8888/Strime/Encoding/web/app_dev.php/';
                break;

            default:
                $server_to_use = NULL;
                break;
        }
        return $server_to_use;
    }

}
