<?php
//
// LogoutUser.php: logout user
// Project: STS - Specialised Test Setter
//

class LogoutUser extends Page
{
	protected function analyse($content)
	{
		// If the user isn't logged in, then warn them ...
		if (!StateLogin::getInstance()->isLoggedIn())
		{
			throw new Exception("You are not logged in!", E_NOTICE);
		}

		// Set the login level to an appropriate value.
		StateLogin::getInstance()->logOut();

		// Ensure that the fact of this logout is acknowledged.
		$content->setGreeting("You are now logged out");

		// Destroy the session itself, then the cookie ...
		// CAUTION REQUIRED: this destroys all session values, including, e.g., the
		// active state machine. As life doesn't actually completely cease at logoff,
		// this can cause unforseen state problems. The active state machine problem
		// was solved by giving it a decent default.
		session_destroy();
		setcookie(session_name(), '', time() - 3600);

		// Nothing more to be done ...
		return false;
	}
}
