<?php
//
// Connection.php: project-specific database connection
// Project: STS - Specialised Test Setter
//

class Connection
{
	const host = "localhost";
	const dbn = "sorede5_sts";
	const edbn = "sorede5_sqs";
	const usr = "sorede5_rt";
	const password = "tondu66690";

	static function makeDbx($experimental)
	{
		$dbx = new PDO(
			"mysql:host=".self::host.";dbname=".($experimental?self::edbn:self::dbn),
			self::usr,
			self::password,
			array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
		);
		// Make sure that exceptions are thrown wherever possible.
		$dbx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbx;
	}
}
