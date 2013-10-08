<?php
//
// EmptyDatabase.php: deletes, then (re-)creates all database tables
// Project: STS - Specialised Test Setter
//

class EmptyDatabase extends Page
{
	protected function analyse($content)
	{
		// Firstly, get out of the emergency or logged-in state ...
		StateLogin::getInstance()->logOut();

		// Need the database connection ...
		$dbx = Connection::makeDbx(false);

		$tables = DatabaseStructure::getOldTables();
		foreach ($tables as $table => $columns)
		{
			// Firstly delete any existing table with the given name.
			$sql = "DROP TABLE IF EXISTS $table;";
			$stmt = $dbx->prepare($sql);
			$stmt->execute();
		}

		$tables = DatabaseStructure::getTables();
		foreach ($tables as $table => $columns)
		{
			// Firstly delete the existing table
			$sql = "DROP TABLE IF EXISTS $table;";
			// Now create a new one: it will be empty
			$sql = "CREATE TABLE $table ($columns)
			ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			$stmt = $dbx->prepare($sql);
			$stmt->execute();
		}

		$tables = DatabaseStructure::getConstraints();
		$index = 0;
		foreach ($tables as $table => $constraint)
		{
			$name = $table . "_ibfk_" . ++$index;
			//++$index;
			$sql = "ALTER TABLE $table ADD CONSTRAINT `$name` FOREIGN KEY $constraint;";
			$stmt = $dbx->prepare($sql);
			$stmt->execute();
		}

		// Insert special users into the user table. The same SQL statement does for
		// all such users ...
		$sql =
			"INSERT INTO user (emailAddr, password, firstName, lastName, level, added) " .
			"VALUES (:emailAddr, :password, :firstName, :lastName, :level, now());";
		$stmt = $dbx->prepare($sql);
		$xsql =
			"INSERT INTO setter (idUser) " .
			"VALUES (:idUser);";
		$xstmt = $dbx->prepare($xsql);

		// Get the users from the preset array ...
		$users = DatabaseStructure::getUsers();

		// Substitute the values as appropriate from the array ...
		foreach ($users as $user => $values)
		{
			$ea = $values["emailAddr"];
			$pw = $values["password"];
			$fn = $user;
			$ln = "user";
			$level = $values["level"];
			$stmt->execute(array(
				":emailAddr" => $ea,
				":password" => $pw,
				":firstName" => $fn,
				":lastName" => $ln,
				":level" => $level,
			));

			$idUser = $dbx->lastInsertId();
			$xstmt->execute(array(
				":idUser" => $idUser,
			));
		}

		// Everything is ok ...
		$content->setGreeting
		(
			"An empty database has been created.",
			"You have been logged out."
		);

		// Nothing more to be done ...
		return false;
	}
}
