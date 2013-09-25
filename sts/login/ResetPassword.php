<?php
//
// ResetPassword.php: reset password, after user has forgotten it
// Project: STS - Specialised Test Setter
//

class ResetPassword extends Page
{
	protected function analyse($content)
	{
		// Set up info for when this HTML page is shown ...
		$content->setGreeting(
			"Reset Password",
			"Enter your email address below, and your password will be reset"
		);

		// If the form has not yet been issued, then do so ...
		if (State::shouldIssueForm())
		{
			$this->issueForm($content);
			return true;
		}

		// Get posted value, making sure it is trimmed
		$emailAddr = Request::trim("emailAddr");
		if (empty($emailAddr))
		{
			throw new Exception("You forgot to enter your email address", E_NOTICE);
		}

		// Need the database connection...
		$dbx = Connection::makeDbx(false);

		// Check whether the email address is known ...
		$sql = "SELECT idUser FROM user WHERE emailAddr=:emailAddr;";
		$stmt = $dbx->prepare($sql);
		$stmt->bindColumn("idUser", $idUser);
		$stmt->execute(array(":emailAddr" => $emailAddr));
		if (!$stmt->fetch(PDO::FETCH_BOUND))
		{
			throw new Exception("The entered email address does not match any in the database", E_WARNING);
		}

		// Create a new, random password
		$password = substr(md5(uniqid(rand(), true)), 3, 10);

		// Update to the new password ...
		$sql = "UPDATE user SET password=SHA1(:password) WHERE idUser=:idUser LIMIT 1;";
		$stmt = $dbx->prepare($sql);
		$stmt->execute(array(
			":idUser" => $idUser,
			":password" => $password,
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
			"A temporary password has been sent to the email address with which you " .
			"registered. Once you have logged in with this password, you may change " .
			"it by clicking on the \"Change Password\" link"
		);

		// Construct an email, and send it, or, in the case of a local server,
		// dispose of it by sending an HTML page to simulate it.
		$subject = "Your temporary password";
		$host = "STS";
		$body =
"Your password to log into $host has been temporarily changed to: \"$password\".
Please log in using this password and this email address. You should then change
your password to something more familiar.";
		$email = new SendEmail($emailAddr, $subject, $body);
		$email->send();

		// Nothing more to be done ...
		return false;
	}

	public function issueForm($content)
	{
		// Compose the various name/value pairs and text outputs ...
		$txtEmailAddr = "Email Address";
		$nameEmailAddr = "emailAddr";
		$valueEmailAddr = Request::recall($nameEmailAddr);
		$txtResetPassword = "Reset Password";
		$nameResetPassword = "btnResetPassword";
		$txtResetPassword = "Change Password";
		$action = Url::make(State::getPageBuilder());

		// Set the following html up as the content of the main form div ...
		$content->formHtml = <<<END_HTML
<form class='form' class='stand' action='$action' method='post'>
<table>
<tr>
<td><span>$txtEmailAddr:</span></td>
<td><input type='email' name='$nameEmailAddr' value='$valueEmailAddr' size='30' /></td>
</tr>
</table>
<div class='buttons'>
<input type='submit' name='$nameResetPassword' value='$txtResetPassword'></input>
</div>
</form>
END_HTML;
	}
}
