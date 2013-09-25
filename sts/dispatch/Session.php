<?php
//
// Session.php: class containing static functions to do with $_SESSION
// Project: STS - Specialised Test Setter
//

class Session
{
	public static function get($name, $default = null)
	{
		if (isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}
		return $default;
	}

	public static function exists($name)
	{
		return isset($_SESSION[$name]);
	}

	public static function set($name, $value)
	{
		$_SESSION[$name] = $value;
		return $value;
	}

	public static function transfer($name)
	{
		$value = null;
		if (isset($_SESSION[$name]))
		{
			$value = $_SESSION[$name];
			unset($_SESSION[$name]);
		}
		return $value;
	}

	public static function forget($name)
	{
		if (isset($_SESSION[$name]))
		{
			unset($_SESSION[$name]);
		}
	}
}
