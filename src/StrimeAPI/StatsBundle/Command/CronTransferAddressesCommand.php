<?php

namespace StrimeAPI\StatsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\UserBundle\Entity\Address;

class CronTransferAddressesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cron:addresses:transfer')
            ->setDescription('Transfer the addresses from users and geocode them')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Set the dates
        // $fromDate = new \DateTime('now');
        $from_date = new \DateTime('first day of January 2015');
        $from_date->setTime(0, 0, 0);

        $to_date = clone $from_date;
        $to_date->modify('+1 day');

        while( new \DateTime() > $to_date ) {

            // Get the users created
            $query = $em->createQueryBuilder();
            $query->select( 'api_user' );
            $query->from( 'StrimeAPIUserBundle:User','api_user' );
            $query->where('api_user.created_at >= :from_date');
            $query->andWhere('api_user.created_at < :to_date');
            $query->setParameter('from_date', $from_date);
            $query->setParameter('to_date', $to_date);
            $users = $query->getQuery()->getResult();

            // If users have been created during the period
            if(($users != NULL) && is_array($users)) {

                // For each user
                foreach ($users as $user) {

                    // Check if there is already an address for this user
                    $address = $em->getRepository('StrimeAPIUserBundle:Address')->findOneBy(array('user' => $user));

                    if($address == NULL) {

                        // Define the address as a single string
                        $complete_address = "";

                        if($user->getAddress() != NULL)
                            $complete_address .= $user->getAddress();

                        if(($user->getAddressMore() != NULL) && ($user->getAddress() != NULL))
                            $complete_address .= ", " . $user->getAddressMore();
                        elseif(($user->getAddressMore() != NULL) && ($user->getAddress() == NULL))
                            $complete_address .= $user->getAddressMore();
                        
                        if(($user->getZip() != NULL) && (strlen($complete_address) > 0))
                            $complete_address .= ", " . $user->getZip();
                        elseif(($user->getZip() != NULL) && (strlen($complete_address) == 0))
                            $complete_address .= $user->getZip();
                        
                        if(($user->getCity() != NULL) && ($user->getZip() == NULL) && (strlen($complete_address) > 0))
                            $complete_address .= ", " . $user->getCity();
                        elseif(($user->getCity() != NULL) && ($user->getZip() == NULL) && (strlen($complete_address) == 0))
                            $complete_address .= $user->getCity();
                        elseif(($user->getZip() != NULL) && ($user->getZip() != NULL))
                            $complete_address .= $user->getCity();

                        if(($user->getCountry() != NULL) && (strlen($complete_address) > 0))
                            $complete_address .= ", " . $user->getCountry();

                        // If the address is not just a country or less
                        if(strlen($complete_address) > 0) {

                            // Geocode their address
                            $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
                            $geocoder = new \Geocoder\Provider\GoogleMaps($curl);

                            try {
                                $results = $geocoder->geocode( $complete_address );

                                // Get the latitude and longitude
                                /* $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
                                $this->assertCount(1, $results); */
                                $result = $results->first();
                                // $this->assertInstanceOf('\Geocoder\Model\Address', $result);
                                $latitude = $result->getLatitude();
                                $longitude = $result->getLongitude();

                                // Save it in the database
                                $address = new Address;
                                $address->setUser($user);
                                $address->setAddress( $user->getAddress() );
                                $address->setAddressMore( $user->getAddressMore() );
                                $address->setState( $user->getState() );
                                $address->setZip( $user->getZip() );
                                $address->setCity( $user->getCity() );
                                $address->setCountry( $user->getCountry() );
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
                            $output->writeln( "[".date("Y-m-d H:i:s")."] ".$user->getEmail() ." : address not detailed enough" );
                        }
                    }

                    // If a geocoded address already exists for this user
                    else {
                        
                        // Send a message back to console
                        $output->writeln( "[".date("Y-m-d H:i:s")."] ".$user->getEmail() ." : address already geocoded" );
                    }
                }
            }

            // Move the dates
            $from_date->modify('+1 day');
            $to_date->modify('+1 day');
        }

        $output->writeln( "[".date("Y-m-d H:i:s")."] OK: The data has been updated." );
    }
}