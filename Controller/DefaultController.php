<?php

namespace BSP\AdyenBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BSPAdyenBundle:Default:index.html.twig', array('name' => $name));
    }
}
