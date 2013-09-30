<?php
//
// RegisterAccount.php: register an account
// Project: STS - Specialised Test Setter
//

class RegisterAccount extends Page
{
	protected function analyse($content)
	{
		// Set up info for when this HTML page is shown ...
		$content->setGreeting("Register Account");

		// If the form has not yet been issued, then do so ...
		if (State::shouldIssueForm())
		{
			$this->issueForm($content);
			return true;
		}

		// Get posted values, making sure they are trimmed
		$firstName = Request::trim("firstName");
		$lastName = Request::trim("lastName");
		$emailAddr = Request::trim("emailAddr");
		$password = Request::trim("password");
		$password2 = Request::trim("password2");

		// Check for a first name ...
		if (!preg_match('/^[A-Z \'.-]{2,20}$/i', $firstName))
		{
			throw new Exception("Please enter your first name", E_NOTICE);
		}
		// Check for a last name ...
		if (!preg_match('/^[A-Z \'.-]{2,40}$/i', $lastName))
		{
			throw new Exception("Please enter your last name", E_NOTICE);
		}
		// Check for an email address ...
		if (!filter_var($emailAddr, FILTER_VALIDATE_EMAIL))
		{
			throw new Exception("Please enter a valid email address", E_NOTICE);
		}
		// Check for a password and match between the two passwords ...
		if (!preg_match('/^\w{4,20}$/', $password))
		{
			throw new Exception("Please enter a valid password", E_NOTICE);
		}
		if ($password != $password2)
		{
			throw new Exception("Your password did not match the original one", E_NOTICE);
		}

		// Need the database connection...
		$dbx = Connection::makeDbx(false);

		// Make sure the email address is available ...
		$sql = "SELECT idUser FROM user WHERE emailAddr=:emailAddr;";
		$stmt = $dbx->prepare($sql);
		$stmt->execute(array(":emailAddr" => $emailAddr));

		// We should have no rows, which would mean that the email is available.
		if ($stmt->fetch(PDO::FETCH_NUM))
		{
			throw new Exception("That email address has already been registered", E_NOTICE);
		}

		// Create the activation code ...
		$actCode = md5(uniqid(rand(), true));

		// Add the user to the database ...
		$sql = 
			"INSERT INTO user (emailAddr, password, firstName, lastName, actCode, added) " .
			"VALUES (:emailAddr, SHA1(:password), :firstName, :lastName, :actCode, now());";
		$stmt = $dbx->prepare($sql);
		$stmt->execute(array(
			":emailAddr" => $emailAddr,
			":password" => $password,
			":firstName" => $firstName,
			":lastName" => $lastName,
			":actCode" => $actCode,
		));

		// Add the user to the setter table. This will change, since not all users will
		// automatically be teachers (setters) ...
		$idUser = $dbx->lastInsertId();
		$sql = 
			"INSERT INTO setter (idUser) " .
			"VALUES (:idUser);";
		$stmt = $dbx->prepare($sql);
		$stmt->execute(array(
			":idUser" => $idUser,
		));

		
		// Everything is ok past this point. There is therefore no form to correct.
		State::closeForm();

		// Provide info for the user for the next time an HTML page is shown ...
		$content->setGreeting
		(
			"Thank you for registering. " .
			"A confirmation email has been sent to your address. " .
			"Please click on the link in that email in order to activate your account."
		);

		// Construct an email, and send it, or, in the case of a local server,
		// dispose of it by sending an HTML page to simulate it.
		$subject = "Confirm Registration";
		$url = Url::make("ActivateAccount", array("x" => $actCode));
		$host = "STS";
		$body =
			"Thank you for registering at $host.\n" .
			"To activate your account, please click on this link:\n$url\n";
		$email = new SendEmail($emailAddr, $subject, $body);
		$email->send();

		// Nothing more to be done ...
		return false;
	}

	public function issueForm($content)
	{
		// Compose the various name/value pairs and text outputs ...
		$txtFirstName = "First Name";
		$nameFirstName = "firstName";
		$valueFirstName = Request::recall($nameFirstName);
		$txtLastName = "Last Name";
		$nameLastName = "lastName";
		$valueLastName = Request::recall($nameLastName);
		$txtEmailAddr = "Email Address";
		$nameEmailAddr = "emailAddr";
		$valueEmailAddr = Request::recall($nameEmailAddr);
		$txtPassword = "Password";
		$namePassword = "password";
		$valuePassword = Request::recall($namePassword);
		$txtPassword2 = "Confirm Password";
		$namePassword2 = "password2";
		$valuePassword2 = Request::recall($namePassword2);
		$nameRegister = "btnRegister";
		$txtRegister = "Register";
		$action = Url::make(State::getPageBuilder());

		// Set the following html up as the content of the main form div ...
		$content->formHtml = <<<END_HTML
<form class='form' class='stand' action='$action' method='post'>
<table>
<tr>
<td><span>$txtFirstName:</span></td>
<td><input type='text' name='$nameFirstName' value='$valueFirstName' size='20' /></td>
</tr>
<tr>
<td><span>$txtLastName:</span></td>
<td><input type='text' name='$nameLastName' value='$valueLastName' size='20' /></td>
</tr>
<tr>
<td><span>$txtEmailAddr:</span></td>
<td><input type='email' name='$nameEmailAddr' value='$valueEmailAddr' size='30' /></td>
</tr>
<tr>
<td><span>$txtPassword:</span></td>
<td><input type='password' name='$namePassword' value='$valuePassword' size='20' /></td>
</tr>
<tr>
<td><span>$txtPassword2:</span></td>
<td><input type='password' name='$namePassword2' value='$valuePassword2' size='20' /></td>
</tr>
</table>
<div class='buttons'>
<input type='submit' name='$nameRegister' value='$txtRegister'></input>
</div>
</form>
END_HTML;
	}
}
