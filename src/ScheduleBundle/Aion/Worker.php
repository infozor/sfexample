<?php

namespace ScheduleBundle\Aion;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use QueueBundle\Aion\ScheduleQueue;

class Worker
{
	function __construct($container, $output)
	{
		$this->container = $container;
		$this->output = $output;
		$this->conn = $this->container->get('doctrine.dbal.db1_connection');
		$this->conn2 = $this->container->get('doctrine.dbal.db2_connection');
		
		$this->objScheduleQueue = new ScheduleQueue($container, $output);
		
	}
	function do_it($path)
	{
		// @todo id240 SFBACKOFFICE
		$this->watch();
		
	}
	
	// просмотр расписания
	function watch()
	{
		
		
		// 1. Делаем запись в watch_schedule
		$watch_last_insert_id = $this->insertWatchSchedule(1);
		
		// 2. Считываем расписание
		$schedule = $this->getSchedule();
		
		if (count($schedule) > 0)
		{
			$this->updateStatusWatchSchedule($watch_last_insert_id, 2);
		}
		
		for($i = 0; $i < count($schedule); $i++)
		{
			$schedule_id = $schedule[$i]['id'];
			$schedule_time = $this->getScheduleTime($schedule_id);
			$watch_data_last_insert_id = $this->insertWatchScheduleData($watch_last_insert_id, $schedule_id, 1);
			
			$croneStr = $this->getCroneStr($schedule_time[0]);
			
			$check = $this->checkDateTime($croneStr);
			
			if ($check)
			{	
				//$body = $this->getBody($schedule[$i]);
				
				$schedule_id = $schedule[$i]['id'];
				
				
				$this->updateStatusWatchScheduleData($watch_data_last_insert_id, $schedule_id,  3);
				try
				{
					//$output = $this->process($body);
					
					$output = $this->insertInQueue($schedule_id);
					
					$this->updateStatusWatchScheduleData($watch_data_last_insert_id, $output, 4);
				}
				catch ( \Exception $e )
				{
					$exception = $e->getMessage();
					$this->updateStatusWatchScheduleData($watch_data_last_insert_id, $exception, 5);
					throw new \Exception($exception);
				}
				
			}
			else
			{
				$this->updateStatusWatchScheduleData($watch_data_last_insert_id, 'passed', 2);
			}
			
		}
	}
	
	function insertInQueue($id)
	{
		$this->objScheduleQueue->insert($id, 1);
		return 'inserted';
	}
	
	

	
	
	function getCroneStr($param)
	{
		$str = $param['minute'].' '.$param['hour'].' '.$param['day_of_month'].' '.$param['month'].' '.$param['day_of_week'];
		return $str;
	}
	
	function checkDateTime($croneStr)
	{
		$str = $croneStr . ' *';
		
		$schedule = CronSchedule::fromCronString($str);
		
		//$next = $schedule->next();
		//$previous = $schedule->previous();
		
		$current = $schedule->current();
		
		$today = getdate();
		
		if ($today['minutes'] == $current[0])
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function insertWatchSchedule($status_id)
	{
		$sqlstr = sprintf('
				INSERT INTO 
				  public.schedule_watch
				(
				  created_at,
				  updated_at,
				  status_id
				)
				VALUES (
				  NOW(), --created_at
				  NOW(), --updated_at
				  %s --status_id
				);
			', $status_id);
		
		$stmt = $this->conn->prepare($sqlstr);
		$stmt->execute();
		
		$stmt = $this->conn->prepare("SELECT currval('schedule_watch_id_seq')");
		$stmt->execute();
		$result = $stmt->fetchAll();
		$last_insert_id = $result[0]['currval'];
		
		return $last_insert_id;
	}
	function insertWatchScheduleData($schedule_watch_id, $schedule_id, $status_id)
	{
		$sqlstr = sprintf('
					INSERT INTO 
					  public.schedule_watch_data
					(
					  schedule_watch_id,
				      schedule_id,
					  created_at,
					  updated_at,
					  status_id
					)
					VALUES (
					  %s,    --schedule_watch_id
				      %s,    --schedule_id
					  NOW(), --created_at
					  NOW(), --updated_at
					  %s     --status_id
					);
				
			', $schedule_watch_id, $schedule_id, $status_id);
		
		$stmt = $this->conn->prepare($sqlstr);
		$stmt->execute();
		
		$stmt = $this->conn->prepare("SELECT currval('schedule_watch_data_id_seq')");
		$stmt->execute();
		$result = $stmt->fetchAll();
		$last_insert_id = $result[0]['currval'];
		
		return $last_insert_id;
	}
	function updateStatusWatchSchedule($id, $status_id)
	{
		$sqlstr = sprintf('
			UPDATE
			  public.schedule_watch
			SET
			  updated_at = NOW(),
			  status_id = %s
			WHERE
			  id = %s
			', $status_id, $id);
		
		$stmt = $this->conn->prepare($sqlstr);
		$stmt->execute();
	}
	function getSchedule()
	{
		$sqlstr = sprintf('
			SELECT 
			  public.schedule.id,
			  public.schedule.type_id,
			  public.schedule.name,
			  public.schedule.description,
			  public.schedule.body,
			  public.schedule.status_id,
			  public.schedule.active,
			  public.schedule.created_at,
			  public.schedule.updated_at,
			  public.schedule.body_test	
			FROM
			  public.schedule
			WHERE
			  public.schedule.active = true
			');
		
		$fetch = $this->conn->fetchAll($sqlstr);
		
		return $fetch;
	}
	function getScheduleTime($schedule_id)
	{
		$sqlstr = sprintf('
			SELECT 
			  public.schedule_time.id,
			  public.schedule_time.schedule_id,
			  public.schedule_time.date_time,
			  public.schedule_time.minute,
			  public.schedule_time.hour,
			  public.schedule_time.day_of_month,
			  public.schedule_time.month,
			  public.schedule_time.day_of_week,
			  public.schedule_time.type_id,
			  public.schedule_time.cond_id,
			  public.schedule_time.created_at,
			  public.schedule_time.updated_at
			FROM
			  public.schedule_time
			WHERE
			  public.schedule_time.schedule_id = %s
			LIMIT 1	
			', $schedule_id);
		
		$fetch = $this->conn->fetchAll($sqlstr);
		
		return $fetch;
	}
	function updateStatusWatchScheduleData($id, $result, $status_id)
	{
		$sqlstr = sprintf('
			UPDATE
			  public.schedule_watch_data
			SET
			  updated_at = NOW(),
			  status_id = %s,
			  output = \'%s\'	
			WHERE
			  id = %s
			', $status_id, $result, $id);
		
		$stmt = $this->conn->prepare($sqlstr);
		$stmt->execute();
	}
	
}