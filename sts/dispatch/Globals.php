<?php
//
// Globals.php: class containing static functions to do with $_SESSION
// Project: STS - Specialised Test Setter
//

class Globals
{
	public static function get($name, $default = null)
	{
		if (isset($GLOBALS[$name]))
		{
			return $GLOBALS[$name];
		}
		$GLOBALS[$name] = $default;
		return $default;
	}

	public static function exists($name)
	{
		return isset($GLOBALS[$name]);
	}

	public static function set($name, $value)
	{
		$GLOBALS[$name] = $value;
		return $value;
	}

	public static function transfer($name)
	{
		$value = null;
		if (isset($GLOBALS[$name]))
		{
			$value = $GLOBALS[$name];
			unset($GLOBALS[$name]);
		}
		return $value;
	}

	public static function forget($name)
	{
		if (isset($GLOBALS[$name]))
		{
			unset($GLOBALS[$name]);
		}
	}
}
