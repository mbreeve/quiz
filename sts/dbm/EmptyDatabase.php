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

		// Insert special users into the user table. The same SQL statement does for
		// all such users ...
		$sql =
			"INSERT INTO user (emailAddr, password, firstName, lastName, level, added) " .
			"VALUES (:emailAddr, :password, :firstName, :lastName, :level, now());";
		$stmt = $dbx->prepare($sql);

		// Get the users from the preset array ...
		$users = DatabaseStructure::getUsers();

		// Substitute the values as appropriate from the array ...
		foreach ($users as $user => $values)
		{
			$stmt->execute(array(
				":emailAddr" => $values["emailAddr"],
				":password" => $values["password"],
				":firstName" => $user,
				":lastName" => "user",
				":level" => $values["level"],
			));
		}

		// Everything is ok ...
		$content->setGreeting
		(
			"An empty database has been created",
			"You have been logged out"
		);

		// Nothing more to be done ...
		return false;
	}
}
