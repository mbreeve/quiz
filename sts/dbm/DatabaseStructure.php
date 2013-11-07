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
			"test_question" => "",
			"question" => "",
			"test_key" => "",
			"test" => "",
			"setter" => "",
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
  `reply` tinytext NOT NULL COMMENT 'the answer/option text',
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
  `identifier` varchar(80) NOT NULL COMMENT 'name as it appears in the treetable',
  `query` tinytext NOT NULL COMMENT 'the question text',
  `correct` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'correct option for multiple choice',
  PRIMARY KEY (`idQuestion`)
		",

		"setter" => "
  `idSetter` int(10) unsigned NOT NULL,
  `identifier` varchar(80) NOT NULL COMMENT 'name as it appears in the treetable',
  PRIMARY KEY (`idSetter`)
		",

		"test" => "
  `idTest` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idSetter` int(10) unsigned NOT NULL,
  `identifier` varchar(80) NOT NULL COMMENT 'name as it appears in the treetable',
  `descr` tinytext,
  `added` datetime NOT NULL,
  PRIMARY KEY (`idTest`),
  UNIQUE KEY `Setter` (`idSetter`,`identifier`)
		",

		"test_key" => "
  `idTest` int(10) unsigned NOT NULL,
  `indexSetter` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `Test` (`idTest`,`indexSetter`)
		",

		"test_question" => "
  `idQuestion` int(10) unsigned NOT NULL,
  `idTest` int(10) unsigned NOT NULL,
  `sequence` double NOT NULL COMMENT 'order of questions within test',
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

	public static function getConstraints()
	{
		return array
		(
			"answer" => array
			(
				"(`idQuestion`) REFERENCES `question` (`idQuestion`) ON DELETE CASCADE",
			),
			"keyword" => array
			(
				"(`idSetter`) REFERENCES `setter` (`idSetter`) ON DELETE CASCADE",
			),
			"setter" => array
			(
				"(`idSetter`) REFERENCES `user` (`idUser`) ON DELETE CASCADE",
			),
			"test" => array
			(
				"(`idSetter`) REFERENCES `setter` (`idSetter`) ON DELETE CASCADE",
			),
			"test_key" => array
			(
				"(`idTest`) REFERENCES `test` (`idTest`) ON DELETE CASCADE",
			),
			"test_question" => array
			(
 				"(`idTest`) REFERENCES `test` (`idTest`) ON DELETE CASCADE",
 				"(`idQuestion`) REFERENCES `question` (`idQuestion`) ON DELETE CASCADE",
 			),
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

			"setter" => array
			(
				"emailAddr" => "setter@x.com",
				"password" => "02ac648906a6828f331d73e868fceca24e06da77",
				"level" => StateLogin::setter,
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
