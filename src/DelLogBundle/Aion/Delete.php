<?php

namespace DelLogBundle\Aion;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Delete
{
	function __construct($container, $output)
	{
		$this->container = $container;
		$this->output = $output;
		$this->conn = $this->container->get('doctrine.dbal.db1_connection');
	}
	function do_it($path, $list_dir)
	{
		// @todo id188
		foreach ( $list_dir as $dir )
		{
			$dir_path = $path.'/'.$dir;
			
			if (file_exists($dir_path))
			{
				foreach ( new \DirectoryIterator($dir_path) as $fileInfo )
				{
					if (!$fileInfo->isDot())
					{
						unlink($fileInfo->getPathname());
					}
				}
			}
		}
	}
}