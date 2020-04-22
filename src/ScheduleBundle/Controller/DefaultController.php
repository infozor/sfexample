<?php

namespace ScheduleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ScheduleBundle:Default:index.html.twig');
    }
}
