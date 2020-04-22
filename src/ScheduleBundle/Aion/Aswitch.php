<?php

namespace ScheduleBundle\Aion;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Aswitch
{
	function __construct($container, $output)
	{
		$this->container = $container;
		$this->output = $output;
		$this->conn = $this->container->get('doctrine.dbal.db1_connection');
	}
	function do_it($path, $arg)
	{
		// @todo id238 SFBACKOFFICE
		switch ($arg)
		{
			case '1' :
				{
					$table_name = 'products01';
					$port_name = '9307';
					break;
				}
			case '2' :
				{
					$table_name = 'products02';
					$port_name = '9308';
					break;
				}
			
			default :
				{
					$table_name = 'products01';
					$port_name = '9307';
					break;
				}
		}
		
		try
		{
			
			$this->conn->beginTransaction();
			
			$value = $table_name;
			$key = 'table';
			
			$sqlstr = sprintf('
			UPDATE 
			  public.site_sphinx_config
			SET
              value = \'%s\', --value   
			  updated_at = NOW() --updated_at
			 
			WHERE
			 
			  public.site_sphinx_config.key = \'%s\'
			', $value, $key);
			
			$stmt = $this->conn->prepare($sqlstr);
			$stmt->execute();
			
			$value = $port_name;
			$key = 'port';
			
			$sqlstr = sprintf('
			UPDATE
			  public.site_sphinx_config
			SET
              value = \'%s\', --value
			  updated_at = NOW() --updated_at
		
			WHERE
		
			  public.site_sphinx_config.key = \'%s\'
			', $value, $key);
			
			$stmt = $this->conn->prepare($sqlstr);
			$stmt->execute();
			
			$sqlstr = sprintf('
	        UPDATE
			  public.sphinx_config
			SET
			  value = \'%s\' --value
			WHERE
			  key = \'%s\'	
			', $value, $key);
			
			$stmt = $this->conn->prepare($sqlstr);
			$stmt->execute();
		
			$sqlstr = sprintf('
				SELECT
				  *
				FROM
				  "ion_UpdateViewProduct"();
			', $value, $key);
			
			$fetch = $this->conn->fetchAll($sqlstr);
			
			
			$this->conn->commit();
		}
		catch ( \Exception $e )
		{
			$this->conn->rollback();
			throw new \Exception($e->getMessage());
		}
		
		
	}
}