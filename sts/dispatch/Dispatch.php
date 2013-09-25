<?php
//
// Dispatch.php: dispatch to an appropriate html or json builder
// Project: STS - Specialised Test Setter
//

class Dispatch
{
	public static function go()
	{
		$page = Request::get("page");
		$json = Request::get("json");

		if (empty($page) || !class_exists($page))
		{
			$page = "HomePage";
		}
		while ($page)
		{
			$builder = new $page();
			if (!$builder instanceOf Page)
			{
				$page = "HomePage";
				$builder = new HomePage();
			}
			State::setPageBuilder($page);
			if ($json)
			{
				$builder->getJson();
				$page = null;
			}
			else
			{
				$page = $builder->execute();
			}
		}
	}
}
