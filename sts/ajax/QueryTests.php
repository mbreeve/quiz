<?php
//
// QueryTests.php: looks up tests & questions from the database: returns json
// Project: STS - Specialised Test Setter
//

class QueryTests extends Page
{
	public function getJson()
	{
		// Initialise the response data ...
		$json["tasks"] = array();
		// Get the tasks "HTML input" from the javascript client ...
		$tasks = json_decode(Request::get("tasks"), true);
		foreach ($tasks as $task)
		{
			$class = $task["class"];
			$method = $task["method"];
			$task["success"] = true;

			try
			{
				$class = new $class();
				$task["db"] = $class->$method($task["args"]);
			}
			catch (Exception $e)
			{
				$task["success"] = false;
				$task["exception"] = $e->getMessage();
			}

			$json["tasks"][] = $task;
		}
		$json["walk"] = print_r($json, true);
		$json["args"] = print_r($tasks, true);
		echo json_encode($json);
	}
}
