<?php
//
// Request.php: class containing static functions to do with $_REQUEST
// Project: STS - Specialised Test Setter
//

class Request
{
	public static function get($name, $default = null)
	{
		return isset($_REQUEST[$name]) ? stripslashes($_REQUEST[$name]) : $default;
	}

	public static function recall($name)
	{
		return Session::get("stateRefill") && isset($_REQUEST[$name]) ? $_REQUEST[$name] : "";
	}

	public static function trim($name)
	{
		return trim(self::recall($name));
	}

	public static function exists($name)
	{
		return !empty($_REQUEST[$name]);
	}

	public static function forget($name)
	{
		if (isset($_REQUEST[$name]))
		{
			unset($_REQUEST[$name]);
		}
	}
}
