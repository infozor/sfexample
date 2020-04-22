<?php

namespace ScheduleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use ScheduleBundle\Aion\Worker;


class WorkerCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('schedule:worker')->setDescription('description schedule:worker')->addArgument('arg', InputArgument::OPTIONAL, 'Argument')->addOption('opt', null, InputOption::VALUE_NONE, 'Option')->setHelp(<<<EOT
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
			
			$Worker = new Worker($this->getContainer(), $output);
			
			
			$Worker->do_it($log_path);
			
			
			
			$status_text = 'ok';
		}
		catch ( \Exception $e )
		{
			$status_text = $e->getMessage();
		}
		
		$message = date('d.m.Y H:i:s') . ' ' . $status_text . "\n";
		
		$this->log('load_', $message);
		
		$output->writeln($status_text);
	}
	public function log($type, $message)
	{
		$log_path = $this->get_log_path();
		$path = $log_path . "/"."ScheduleCommand";
		if (!file_exists($path))
		{
			mkdir($path);
		}
		
		$file = $path . '/' . $type . "worker" . ".log";
		return ( bool ) file_put_contents($file, $message, FILE_APPEND) . "\n";
	}
	function get_log_path()
	{
		global $kernel;
		$path = realpath($kernel->getRootDir() . "/../var/logs");
		return $path;
	}
}