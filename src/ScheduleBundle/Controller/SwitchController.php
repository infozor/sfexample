<?php

namespace ScheduleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Output\BufferedOutput;

class SwitchController extends Controller
{

	public function indexAction()
	{
		$data = $this->RunCommand();
		
		return $this->render('ScheduleBundle:Default:tasktest.html.twig', array(
				'data' => $data
		));
		
		
		
	}

	function RunCommand()
	{
		
		
		
		$kernel = $this->get('kernel');
		$application = new Application($kernel);
		$application->setAutoExit(false);
		
		$input = new ArrayInput(array(
				'command' => 'schedule:switch',
				'arg' => '2',
				'--opt' => 'test'
		));
		// You can use NullOutput() if you don't need the output
		$output = new BufferedOutput();
		$application->run($input, $output);
		
		// return the output, don't use if you used NullOutput()
		$content = $output->fetch();
		
		// return new Response(""), if you used NullOutput()
		//return new Response($content);
		return $content;
	}
}
