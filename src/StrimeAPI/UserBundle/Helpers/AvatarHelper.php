<?php

namespace StrimeAPI\UserBundle\Helpers;

use Doctrine\ORM\EntityManager;

class AvatarHelper {



    public function __construct(EntityManager $em) {
        $this->em = $em;
    }



    /**
     * @return null
     */
    public function setUserAvatar($user, $google_user, $facebook_user)
    {
        if($user->getAvatar() != NULL)
            $user_avatar = $user->getAvatar();

        elseif(($google_user != NULL) && ($google_user["google_image"] != NULL))
            $user_avatar = $google_user["google_image"];

        elseif(($facebook_user != NULL) && ($facebook_user["facebook_image"] != NULL))
            $user_avatar = $facebook_user["facebook_image"];

        else
            $user_avatar = NULL;

        return $user_avatar;
    }

}
