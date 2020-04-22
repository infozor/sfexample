<?php

namespace DelLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DelLogBundle:Default:index.html.twig');
    }
}
