<?php
//
// ActivateAccount.php: activate a user after registering him/her.
// Project: STS - Specialised Test Setter
//

class ActivateAccount extends Page
{
	protected function analyse($content)
	{
		// Get activation code ...
		$actCode = Request::get("x");
		$len = strlen($actCode);

		// The activation code should be prcisely 32 chars
		if ($len == 0)
		{
			throw new Exception("Non-existent activation code", E_NOTICE);
		}
		else if ($len != 32)
		{
			throw new Exception("Invalid activation code", E_NOTICE);
		}

		// Need the database connection...
		$dbx = Connection::makeDbx(false);

		// Check whether the activation code will do its thing ...
		$sql = "UPDATE user SET actCode=NULL WHERE actCode=:ac LIMIT 1;";
		$stmt = $dbx->prepare($sql);
		$stmt->execute(array(":ac" => $actCode));
		if ($stmt->rowCount() != 1)
		{
			throw new Exception("The activation code did not work", E_WARNING);
		}

		// Everything is ok ...
		$content->setGreeting
		(
			"Your account is now active",
			"You may now log in"
		);

		// Nothing more to be done ...
		return false;
	}
}
