<?php

namespace ScheduleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use ScheduleBundle\Aion\Aswitch;


class SwitchCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('schedule:switch')->setDescription('description schedule:switch')->addArgument('arg', InputArgument::OPTIONAL, 'Argument')->addOption('opt', null, InputOption::VALUE_NONE, 'Option')->setHelp(<<<EOT
                    The <info>%command.name%</info> command is schedule:worker.

<info>php %command.full_name% [--help] arg </info>

EOT
);
	}
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try
		{
			
			
			$arg = $input->getArgument('arg');
			$result = null;
			
			$log_path = $this->get_log_path();
			
			$Aswitch = new Aswitch($this->getContainer(), $output);
			
			
			$Aswitch->do_it($log_path, $arg);
			
			
			
			$status_text = 'ok';
		}
		catch ( \Exception $e )
		{
			$status_text = $e->getMessage();
		}
		
		$message = date('d.m.Y H:i:s') . ' ' . $status_text . "\n";
		
		$this->log('main_', $message);
		
		$output->writeln($status_text);
	}
	public function log($type, $message)
	{
		$log_path = $this->get_log_path();
		$path = $log_path . "/"."SwitchCommand";
		if (!file_exists($path))
		{
			mkdir($path);
		}
		
		$file = $path . '/' . $type . "switch" . ".log";
		return ( bool ) file_put_contents($file, $message, FILE_APPEND) . "\n";
	}
	function get_log_path()
	{
		global $kernel;
		$path = realpath($kernel->getRootDir() . "/../var/logs");
		return $path;
	}
}