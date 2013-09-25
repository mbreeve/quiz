<?php
//
// LoginUser.php: login user
// Project: STS - Specialised Test Setter
//

class LoginUser extends Page
{
	protected function analyse($content)
	{
		// If the user is already logged in, then warn them ...
		if (StateLogin::getInstance()->isLoggedIn())
		{
			throw new Exception("You are already logged in!", E_NOTICE);
		}

		// Set up info for when this HTML page is shown ...
		$content->setGreeting
		(
			"Login User",
			"Your browser must allow cookies in order to log in"
		);

		// If the form has not yet been issued, then do so ...
		if (State::shouldIssueForm())
		{
			$this->issueForm($content);
			return true;
		}

		// Get posted values, making sure they are trimmed
		$emailAddr = Request::trim("emailAddr");
		$password = Request::trim("password");

		// Validate the password:
		if (empty($password))
		{
			throw new Exception("You forgot to enter your password!", E_NOTICE);
		}

		// Validate the email address:
		if (empty($emailAddr))
		{
			throw new Exception("You forgot to enter your email address!", E_NOTICE);
		}

		// Need the database connection...
		$dbx = Connection::makeDbx(false);

		// Make sure the email address is available...
		$sql =
			"SELECT idUser, firstName, level FROM user " .
			"WHERE emailAddr=:emailAddr AND password=SHA1(:password) AND actCode IS NULL;";
		$stmt = $dbx->prepare($sql);
		$stmt->bindColumn("idUser", $idUser);
		$stmt->bindColumn("firstName", $firstName);
		$stmt->bindColumn("level", $level);
		try
		{
			$stmt->execute(array(
				":emailAddr" => $emailAddr,
				":password" => $password,
			));
		}
		catch (Exception $e)
		{
		}
	
		// We should be able to fetch 1 row ...
		if (!$stmt->fetch(PDO::FETCH_BOUND))
		{
			$users = DatabaseStructure::getUsers();
			$super = $users["super"];
			if (SHA1($password) == $super["password"] && $emailAddr == $super["emailAddr"])
			{
				// Something has gone very wrong with the database. The correct correct
				// password and email address for supervisor level have been provided.
				// Grant zpecial emergency powers to re-create the database.
				// Set the login level to an appropriate value.
				StateLogin::getInstance()->setEmergency();
				$content->setGreeting
				(
					"Database emergency",
					"You have been granted emergency powers to re-create an empty database"
				);
				return false;
			}
			else
			{
				throw new Exception(
"Either the email address or password entered do not match those on file,
or you have not yet activated your account", E_NOTICE);
			}
		}

		// Everything is ok past this point. There is therefore no form to correct.
		State::closeForm();
		// Set the login level to an appropriate value.
		StateLogin::getInstance()->logIn($level, $idUser);

		// Provide info for the user for the next time an HTML page is shown ...
		$content->setGreeting
		(
			"Welcome, $firstName",
			"You are now logged in"
		);

		// Nothing more to be done ...
		return false;
	}

	public function issueForm($content)
	{
		// Compose the various name/value pairs and text outputs ...
		$txtEmailAddr = "Email Address";
		$nameEmailAddr = "emailAddr";
		$valueEmailAddr = Request::recall($nameEmailAddr);
		$txtPassword = "Password";
		$namePassword = "password";
		$valuePassword = Request::recall($namePassword);
		$nameLogin = "btnLogin";
		$txtLogin = "Login";
		$action = Url::make(State::getPageBuilder());

		// Set the following html up as the content of the main form div ...
		$content->formHtml = <<<END_HTML
<form class='form' class='stand' action='$action' method='post'>
<table>
<tr>
<td><span>$txtEmailAddr:</span></td>
<td><input type='email' name='$nameEmailAddr' value='$valueEmailAddr' size='30' /></td>
</tr>
<tr>
<td><span>$txtPassword:</span></td>
<td><input type='password' name='$namePassword' value='$valuePassword' size='20' /></td>
</tr>
</table>
<div class='buttons'>
<input type='submit' name='$nameLogin' value='$txtLogin'></input>
</div>
</form>
END_HTML;
	}
}
