<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\Address;

class CronGeocodeAddressesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:addresses:geocode')
            ->setDescription('Geocode the addresses that have not yet been geocoded')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get the addresses with NULL coordinates
        $query = $em->createQueryBuilder();
        $query->select( 'api_user_address' );
        $query->from( 'StrimeAPIUserBundle:Address','api_user_address' );
        $query->where('api_user_address.latitude IS NULL');
        $query->andWhere('api_user_address.longitude IS NULL');
        $addresses = $query->getQuery()->getResult();

        // If we have results
        if(($addresses != NULL) && is_array($addresses)) {

            // For each address
            foreach ($addresses as $address) {

                // Define the address as a single string
                $complete_address = "";

                if($address->getAddress() != NULL)
                    $complete_address .= $address->getAddress();

                if(($address->getAddressMore() != NULL) && ($address->getAddress() != NULL))
                    $complete_address .= ", " . $address->getAddressMore();
                elseif(($address->getAddressMore() != NULL) && ($address->getAddress() == NULL))
                    $complete_address .= $address->getAddressMore();
                
                if(($address->getZip() != NULL) && (strlen($complete_address) > 0))
                    $complete_address .= ", " . $address->getZip();
                elseif(($address->getZip() != NULL) && (strlen($complete_address) == 0))
                    $complete_address .= $address->getZip();
                
                if(($address->getCity() != NULL) && ($address->getZip() == NULL) && (strlen($complete_address) > 0))
                    $complete_address .= ", " . $address->getCity();
                elseif(($address->getCity() != NULL) && ($address->getZip() == NULL) && (strlen($complete_address) == 0))
                    $complete_address .= $address->getCity();
                elseif(($address->getZip() != NULL) && ($address->getZip() != NULL))
                    $complete_address .= $address->getCity();

                if(($address->getCountry() != NULL) && (strlen($complete_address) > 0))
                    $complete_address .= ", " . $address->getCountry();

                // If the address is not just a country or less
                if(strlen($complete_address) > 0) {

                    // Geocode their address
                    $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
                    $geocoder = new \Geocoder\Provider\GoogleMaps($curl);

                    try {
                        $results = $geocoder->geocode( $complete_address );

                        // Get the latitude and longitude
                        $result = $results->first();
                        $latitude = $result->getLatitude();
                        $longitude = $result->getLongitude();

                        // Save it in the database
                        $address->setLatitude($latitude);
                        $address->setLongitude($longitude);
                        $em->persist( $address );
                        $em->flush();

                        // Send a message back to console
                        $output->writeln( "[".date("Y-m-d H:i:s")."] ".$complete_address ." : ".$latitude." - ".$longitude );
                    }
                    catch (\Exception $e) {
                        $output->writeln( "[".date("Y-m-d H:i:s")."] ".$complete_address ." : No result" );
                    }
                }

                // If the address is unsufficient
                else {

                    // Send a message back to console
                    $output->writeln( "[".date("Y-m-d H:i:s")."] ".$address->getId() .": address not detailed enough" );
                }
            }
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The data has been updated." );
    }
}