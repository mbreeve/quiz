<?php
//
// Autoload ".php: autoloads classes from their eponymous source files
// Project: STS - Specialised Test Setter
//

function __autoload($class)
{
	$pfx = "..";

	$stdDirs = array(
		".",
		"$pfx/ajax",
		"$pfx/dbm",
		"$pfx/dispatch",
		"$pfx/lib",
		"$pfx/login",
		"$pfx/markup",
		"$pfx/prj",
		"$pfx/tests",
	);

	foreach ($stdDirs as $dir)
	{
		$path = "$dir/$class.php";
		if (file_exists($path))
		{
			include $path;
			return true;
		}
	}
}
