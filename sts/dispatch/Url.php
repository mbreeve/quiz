<?php
//
// Url.php: functions to do with generating a URL with arguments for this
// Project: STS - Specialised Test Setter
//

class Url
{
	public static function checkBase()
	{
		if (!Session::exists("baseUrl") && isset($_SERVER["HTTP_REFERER"]))
		{
			$referer = $_SERVER["HTTP_REFERER"];
			$pos = strpos($referer, "?");
			if ($pos !== false)
			{
				$baseUrl = substr($referer, 0, $pos);
				Session::set("baseUrl", $baseUrl);
			}
		}
	}

	public static function make($page, $args = array())
	{
		$url = Session::get("baseUrl", $_SERVER["PHP_SELF"]) . "?page=$page";
		foreach ($args as $name => $value)
		{
			$value = urlencode($value);
			$url .= "&$name=$value";
			//$url .= "&amp;$name=$value";
		}
		return $url;
	}
}
