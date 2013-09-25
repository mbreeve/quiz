<?php
//
// SendEmail.php: handles the sending (or pretend-sending) of an email
// Project: STS - Specialised Test Setter
//

class SendEmail
{
	private $addrTo = "";
	private $addrFrom = "";
	private $addrCc = "";
	private $addrBcc = "";
	private $subject = "";
	private $body = "";

	public function __construct($addrTo = "", $subject = "", $body = "")
	{
		$this->addrTo = $addrTo;
		$this->addrFrom = $_SERVER["SERVER_ADMIN"];
		$this->subject = $subject;
		$this->body = $body;
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
		case "addrTo":
		case "addrFrom":
		case "addrCc":
		case "addrBcc":
		case "subject":
		case "body":
			$this->$name = $value;
			break;
		}
	}

	public function send()
	{
		$headers = "From: $this->addrFrom\n";
		if (!empty($this->addrCc))
		{
			$headers .= "CC: $this->addrCc\n";
		}
		if (!empty($this->addrBcc))
		{
			$headers .= "BCC: $this->addrBcc\n";
		}

		// Send the email. In the case of a local server, it is difficult to send a real
		// email, so just show the content in here (in the page which is being built).
		if (empty($_SERVER["SERVER_NAME"]) || $_SERVER["SERVER_NAME"] != "localhost")
		{
			mail($this->addrTo, $this->subject, $this->body, $headers);
		}
		else
		{
			$email =
				"To: " . $this->addrTo . "\n" .
				$headers .
				"Subject: " . $this->subject . "\n\n" .
				$this->body . "\n";
			$text =
"Since this website is using a localhost, sending real email is not easy.\n" .
"This is what would have been sent:\n\n" .
				$email;
			Session::set("emailDescr", $text);
			$url = Url::make("MimicEmail");
			header("Location: $url");
			exit;
		}
	}
}
