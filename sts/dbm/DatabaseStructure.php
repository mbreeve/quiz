<?php
//
// DatabaseStructure.php: project-specific database structure and settings
// Project: STS - Specialised Test Setter
//

class DatabaseStructure
{
	public static function getOldTables()
	{
		return array
		(
			"answer" => "",
			"keyword" => "",
			"question" => "",
			"setter" => "",
			"test" => "",
			"test_key" => "",
			"test_question" => "",
			"user" => "",
			
			"question_test" => "",
			"testkeyword" => "",
			"grouping" => "",
			"group_key" => "",
		);
	}

	public static function getTables()
	{
		return array
		(
		"answer" => "
		  `idQuestion` int(10) unsigned NOT NULL,
		  `reply` varchar(40) NOT NULL COMMENT 'the answer/option text',
		  `optNum` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'used for multichoice questions',
		  PRIMARY KEY (`idQuestion`)
		",

		"keyword" => "
		  `idSetter` int(10) unsigned NOT NULL,
		  `indexSetter` tinyint(3) unsigned NOT NULL,
		  `theWord` varchar(20) NOT NULL,
		  PRIMARY KEY (`idSetter`,`indexSetter`)
		",

		"question" => "
		  `idQuestion` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `query` varchar(40) NOT NULL COMMENT 'the question text',
		  `correct` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'correct option for multiple choice',
		  PRIMARY KEY (`idQuestion`)
		",

		"setter" => "
		  `idUser` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`idUser`)
		",

		"test" => "
		  `idTest` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `idSetter` int(10) unsigned NOT NULL,
		  `name` varchar(80) NOT NULL,
		  `descr` varchar(80) DEFAULT NULL,
		  `added` datetime NOT NULL,
		  PRIMARY KEY (`idTest`),
		  UNIQUE KEY `Setter` (`idSetter`,`name`)
		",

		"test_key" => "
		  `idTest` int(10) unsigned NOT NULL,
		  `indexSetter` tinyint(3) unsigned NOT NULL,
		  UNIQUE KEY `Test` (`idTest`,`indexSetter`)
		",

		"test_question" => "
		  `idQuestion` int(10) unsigned NOT NULL,
		  `idTest` int(10) unsigned NOT NULL,
		  `sequence` double NOT NULL DEFAULT '0' COMMENT 'order questions in order',
		  PRIMARY KEY (`idQuestion`,`idTest`)
		",

		"user" => "
		  `idUser` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `firstName` varchar(20) NOT NULL,
		  `lastName` varchar(40) NOT NULL,
		  `password` varchar(40) NOT NULL,
		  `emailAddr` varchar(80) NOT NULL,
		  `level` tinyint(3) unsigned NOT NULL DEFAULT '2',
		  `actCode` char(32) DEFAULT NULL,
		  `added` datetime NOT NULL,
		  PRIMARY KEY (`idUser`),
		  KEY `login` (`emailAddr`,`password`)
		",
		);
	}

	public static function getUsers()
	{
		return array
		(
			"super" => array
			(
				"emailAddr" => "super@x.com",
				"password" => "a933b2d74681bcb3bb7b01f661a907752259fb70",
				"level" => StateLogin::super,
			),

			"admin" => array
			(
				"emailAddr" => "admin@x.com",
				"password" => "d033e22ae348aeb5660fc2140aec35850c4da997",
				"level" => StateLogin::admin,
			),

			"teacher" => array
			(
				"emailAddr" => "teacher@x.com",
				"password" => "4a82cb6db537ef6c5b53d144854e146de79502e8",
				"level" => StateLogin::teacher,
			),

			"learner" => array
			(
				"emailAddr" => "learner@x.com",
				"password" => "b879c6e092ce6406eb1f806bf3757e49981974a7",
				"level" => StateLogin::learner,
			),

			"student" => array
			(
				"emailAddr" => "student@x.com",
				"password" => "204036a1ef6e7360e536300ea78c6aeb4a9333dd",
				"level" => StateLogin::student,
			),

			"browser" => array
			(
				"emailAddr" => "browser@x.com",
				"password" => "ef98362b8a6b0c8cd804b0d227aa1ffeaba89786",
				"level" => StateLogin::browser,
			),
		);
	}
}
