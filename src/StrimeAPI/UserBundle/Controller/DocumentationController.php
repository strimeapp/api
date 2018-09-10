<?php

namespace StrimeAPI\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DocumentationController extends Controller
{
    /**
     * @Route("/doc", name="doc")
     * @Template("StrimeAPIUserBundle:Documentation:index.html.twig")
     */
    public function displayDocAction()
    {
        return array();
    }
}
