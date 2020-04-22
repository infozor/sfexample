<?php

namespace DelLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Output\BufferedOutput;

class DeleteController extends Controller
{

	public function indexAction()
	{
		$data = $this->RunCommand();
		
		return $this->render('DelLogBundle:Default:tasktest.html.twig', array(
				'data' => $data
		));
		
		
		
	}

	function RunCommand()
	{
		
		
		
		$kernel = $this->get('kernel');
		$application = new Application($kernel);
		$application->setAutoExit(false);
		
		$input = new ArrayInput(array(
				'command' => 'log:delete',
				'arg' => '123',
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
