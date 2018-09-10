<?php

namespace StrimeAPI\UserBundle\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

use StrimeAPI\UserBundle\Entity\User;
use StrimeAPI\UserBundle\Entity\Right;
use StrimeAPI\ImageBundle\Entity\ImageComment;
use StrimeAPI\AudioBundle\Entity\AudioComment;
use StrimeAPI\VideoBundle\Entity\Comment;

class UserHelper {

    public $user;
    private $container;
    protected $em;


    public function __construct(EntityManager $em, Container $container) {
        $this->em = $em;
        $this->container = $container;
    }



    /**
     * @return null
     */
    public function setUserRights()
    {
        $rights_list = array();

        if(strcmp($this->user->getRole(), "admin") == 0) {
            $predefined_rights = explode(",", $this->container->getParameter('strime_rights'));
            foreach ($predefined_rights as $right) {
                $rights_list[] = $right;
            }
        }

        if($this->user->getRights() != NULL) {
            $rights = $this->user->getRights();

            foreach ($rights as $right) {

                if(!in_array($right, $rights_list)) {
                    $rights_list[] = $right->getRight();
                }
            }
        }

        return implode(",", $rights_list);
    }



    /**
     * @return null
     */
    public function anonymizeComments()
    {
        $comments = $this->em->getRepository('StrimeAPIImageBundle:ImageComment')->findBy(array('user' => $this->user));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

                // Anonymize the user
                $comment->setUser(NULL);
                $this->em->persist($comment);
                $this->em->flush();
            }
        }

        unset($comments);
        $comments = $this->em->getRepository('StrimeAPIAudioBundle:AudioComment')->findBy(array('user' => $this->user));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

                // Anonymize the user
                $comment->setUser(NULL);
                $this->em->persist($comment);
                $this->em->flush();
            }
        }

        unset($comments);
        $comments = $this->em->getRepository('StrimeAPIVideoBundle:Comment')->findBy(array('user' => $this->user));

        if( is_array($comments) ) {
            foreach ($comments as $comment) {

                // Anonymize the user
                $comment->setUser(NULL);
                $this->em->persist($comment);
                $this->em->flush();
            }
        }

        return;
    }

}
