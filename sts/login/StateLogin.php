<?php
//
// StateLogin.php: state machine controlling startup and login etc.
// Project: STS - Specialised Test Setter
//

class StateLogin extends State
{
	// possible logged-in levels ...
	const out = 0;        // login state is unknown, or user is logged out
	const emergency = 1;  // emergency: impossible to actually log on
	const browser = 2;    // browser level only
	const student = 3;    // takes tests set by somebody else
	const learner = 4;    // sets own tests
	const setter = 5;     // sets tests for students
	const admin = 14;     // administrator
	const super = 15;     // superuser: can do anything (caution!)

	private static $_instance = null;   // static variable to hold single instance

	// Make the constructor private to ensure singleton
	private function __construct() { }

	// A method to get our singleton instance from anywhere, anytime
	public static function getInstance()
	{
		if (!self::$_instance instanceof StateLogin)
		{
			self::$_instance = new StateLogin();
		}
		return self::$_instance;
	}

	public function __get($name)
	{
		switch ($name)
		{
		case "level":
			return Session::get("loginLevel", self::out);
		case "idUser":
			return Session::get("loginIdUser", 0);
		}
	}

	public function logIn($level, $idUser)
	{
		Session::set("loginLevel", $level);
		Session::set("loginIdUser", $idUser);
	}

	public function isLoggedIn()
	{
		return $this->level >= self::browser;
	}

	public function logOut()
	{
		$this->logIn(self::out, 0);
	}

	public function setEmergency()
	{
		$this->logIn(self::emergency, 0);
	}

	public function createMenu($content)
	{
		$level = $this->level;
		$idUser = $this->idUser;
		$urlHomePage = Url::make("HomePage");
		$urlLoginUser = Url::make("LoginUser");
		$urlLogoutUser = Url::make("LogoutUser");
		$urlChangePassword = Url::make("ChangePassword");
		$urlRegisterAccount = Url::make("RegisterAccount");
		$urlResetPassword = Url::make("ResetPassword");
		$urlEmptyDatabase = Url::make("EmptyDatabase");
		$urlLaunchTests = Url::make("LaunchTests");

		$status = <<<END_HTML
<div class='submenu'>level: $level, idUser: $idUser
</div>
END_HTML;

		$emergency = <<<END_HTML
<div class='submenu'>Standard
<a class='menuselectable' href='$urlHomePage'>Home</a>
<a class='menuselectable' href='$urlLoginUser'>Login</a>
<a class='menuselectable' href='$urlEmptyDatabase'>Empty Database</a>
</div>
END_HTML;

		$super = <<<END_HTML
<div class='submenu'>Standard
<a class='menuselectable' href='$urlHomePage'>Home</a>
<a class='menuselectable' href='$urlLogoutUser'>Logout</a>
<a class='menuselectable' href='$urlChangePassword'>Change Password</a>
</div>
<div class='submenu'>Database
<a class='menuselectable' href='$urlEmptyDatabase'>Empty Database</a>
</div>
END_HTML;

		$out = <<<END_HTML
<div class='submenu'>Standard
<a class='menuselectable' href='$urlHomePage'>Home</a>
<a class='menuselectable' href='$urlRegisterAccount'>Register Account</a>
<a class='menuselectable' href='$urlLoginUser'>Login</a>
<a class='menuselectable' href='$urlResetPassword'>Reset Password</a>
</div>
END_HTML;

		$tester = <<<END_HTML
<div class='submenu'>Standard
<a class='menuselectable' href='$urlHomePage'>Home</a>
<a class='menuselectable' href='$urlLogoutUser'>Logout</a>
<a class='menuselectable' href='$urlChangePassword'>Change Password</a>
<a class='menuselectable' href='$urlRegisterAccount'>Register Account</a>
</div>
<div class='submenu'>Test
<a class='menuselectable' href='$urlLaunchTests'>Start Test Management</a>
</div>
END_HTML;

		if ($this->level == self::emergency)
		{
			$content->menuHtml = $status . "\n" . $emergency;
		}
		else if ($this->level == self::super)
		{
			$content->menuHtml = $status . "\n" . $super;
		}
		else if ($this->level < self::browser)
		{
			$content->menuHtml = $status . "\n" . $out;
		}
		else
		{
			$content->menuHtml = $status . "\n" . $tester;
		}
	}
}
