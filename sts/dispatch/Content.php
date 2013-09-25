<?php
//
// Content.php: holds defined content and gets it marked up
// Project: STS - Specialised Test Setter
//

class Content
{
	private static $_instance = null;   // static to hold single instance
	public $menuHtml;
	public $formHtml;
	public $boardHtml;
	public $jsincHtml;
	public $styleHtml;

	// Use this to get the singleton instance from anywhere, anytime
	public static function getInstance()
	{
		if (!self::$_instance instanceof Content)
		{
			self::$_instance = new Content();
		}
		return self::$_instance;
	}

	private function outHtml($html)
	{
		if (!empty($html))
		{
			echo $html . "\n";
		}
	}

	public function setBasicStyle()
	{
		$style = Globals::get("style");
		$this->styleHtml = "<link rel='stylesheet' href='$style/Layout.css' />";
	}

	public function setFatal($fatal)
	{
		if (!empty($fatal))
		{
			// Build the error message:
			$code = $fatal->getCode();
			$file = $fatal->getFile();
			$line = $fatal->getLine();
			$msg = $fatal->getMessage();
			$date = date("d/m/Y H:i:s");
			$this->boardHtml = <<<END_HTML
<div id='divFatal'>
A fatal error was thrown in '$file' on line $line: $msg (code: $code)<br />
Date/Time: $date
</div>
END_HTML;
		}
	}

	public function setWarning($warning)
	{
		if (!empty($warning))
		{
			$this->boardHtml = <<<END_HTML
<div id='divWarning'>$warning<br />
Please try again
</div>
END_HTML;
		}
		return false;
	}

	public function setGreeting($greeting, $comment = null)
	{
		if (empty($greeting))
		{
			// By default, welcome the user ...
			$greeting = "Welcome!";
		}
		if (empty($comment))
		{
			$this->boardHtml = <<<END_HTML
<div id='divGreeting'>
<h2>$greeting</h2>
</div>
END_HTML;
		}
		else
		{
			$this->boardHtml = <<<END_HTML
<div id='divGreeting'>
<h2>$greeting</h2>
$comment
</div>
END_HTML;
		}
	}

	public function buildPage()
	{
		$title = "Specialised Test Setter";
		$this->outHtml(<<<END_HTML
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<title>$title</title>
END_HTML
		);
		$this->outHtml($this->styleHtml);
		$this->setBasicStyle();
		$this->outHtml(<<<END_HTML
</head>
<body>
<div id='divBody'>
<div id='divTitle'>$title</div>
<table border='1'>
<tbody>
<tr>
<td id='tdMain'>
<div id='divMain'>
<div id='divBoard'>
END_HTML
		);
		$this->outHtml($this->boardHtml);
		$this->boardHtml = null;
		$this->outHtml(<<<END_HTML
</div>
<div id='divForm'>
END_HTML
		);
		$this->outHtml($this->formHtml);
		$this->formHtml = null;
		$this->outHtml(<<<END_HTML
</div>
</div>
</td>
<td id='tdMenu'>
<div id='divMenu'>
END_HTML
		);
		$this->outHtml($this->menuHtml);
		$this->menuHtml = null;
		$this->outHtml(<<<END_HTML
</div>
</td>
</tr>
</tbody>
</table>
</div>
END_HTML
		);
		$this->outHtml($this->jsincHtml);
		$this->jsincHtml = null;
		$this->outHtml(<<<END_HTML
</body>
</html>
END_HTML
		);
	}
}
