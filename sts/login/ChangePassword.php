<?php
//
// ChangePassword.php: change a user's password
// Project: STS - Specialised Test Setter
//

class ChangePassword extends Page
{
	protected function analyse($content)
	{
		// Set up info for when this HTML page is shown ...
		$content->setGreeting("Change Password");

		// If the form has not yet been issued, then do so ...
		if (State::shouldIssueForm())
		{
			$this->issueForm($content);
			return true;
		}

		// Get posted values, making sure they are trimmed
		$password1 = Request::trim("password1");
		$password2 = Request::trim("password2");

		// Check for a password and match between the two passwords ...
		if (!preg_match('/^\w{4,20}$/', $password1))
		{
			throw new Exception("Please enter a valid password", E_NOTICE);
		}
		if ($password1 != $password2)
		{
			throw new Exception("Your password did not match the original one", E_NOTICE);
		}

		// Need the database connection...
		$dbx = Connection::makeDbx(false);

		// Update the password ...
		$sql = "UPDATE user SET password=SHA1(:password) WHERE idUser=:idUser LIMIT 1;";
		$stmt = $dbx->prepare($sql);
		$idUser = StateLogin::getInstance()->idUser;
		$stmt->execute(array(
			":idUser" => $idUser,
			":password" => $password1,
		));

		// Exactly one row should have been updated ...
		if ($stmt->rowCount() != 1)
		{
			throw new Exception("Your password could not be changed due to a system error", E_WARNING);
		}

		// Everything is ok past this point. There is therefore no form to correct.
		State::closeForm();

		// Provide info for the user for the next time an HTML page is shown ...
		$content->setGreeting
		(
			"Your password has been changed",
			"You are still logged in"
		);

		// Nothing more to be done ...
		return false;
	}

	public function issueForm($content)
	{
		// Compose the various name/value pairs and text outputs ...
		$txtPassword1 = "New Password";
		$namePassword1 = "password1";
		$valuePassword1 = Request::recall($namePassword1);
		$txtPassword2 = "Confirm New Password";
		$namePassword2 = "password2";
		$valuePassword2 = Request::recall($namePassword2);
		$nameChangePassword = "btnChangePassword";
		$txtChangePassword = "Change Password";
		$action = Url::make(State::getPageBuilder());

		// Set the following html up as the content of the main form div ...
		$content->formHtml = <<<END_HTML
<form class='form' class='stand' action='$action' method='post'>
<table>
<tr>
<td><span>$txtPassword1:</span></td>
<td><input type='password' name='$namePassword1' value='$valuePassword1' size='20' /></td>
</tr>
<tr>
<td><span>$txtPassword2:</span></td>
<td><input type='password' name='$namePassword2' value='$valuePassword2' size='20' /></td>
</tr>
</table>
<div class='buttons'>
<input type='submit' name='$nameChangePassword' value='$txtChangePassword'></input>
</div>
</form>
END_HTML;
	}
}
