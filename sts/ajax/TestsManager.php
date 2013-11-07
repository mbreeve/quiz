<?php
//
// TestsManager.php: called by QueryTests to create and update tests.
// Project: STS - Specialised Test Setter
//

class TestsManager
{
	private $dbx;
	private $ident = "";
	private $descr = "";
	private $newWords = array();
	private $curLinks = array();
	private $reqLinks = array();

	public function loginSetter($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$idLogged = $args["idLogged"];
		$database = array();
		$logged =  $this->readLogged($idLogged);
		$database[] = $logged;
		$database[] = $setters = $this->listSetters($idLogged);
		foreach ($setters["records"] as $setter)
		{
			$idSetter = $setter["idSetter"];
			$database[] = $this->listKeywords($idSetter);
			$database[] = $tests = $this->listTests($idSetter);
			foreach ($tests["records"] as $test)
			{
				$idTest = $test["idTest"];
				$database[] = $this->listQuestions($idSetter, $idTest);
			}
		}
		return array
		(
			"database" => $database,
		);
	}

	public function createTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$idSetter = $args["idSetter"];
		$keywords = $args["keywords"];
		$this->ident = $args["ident"];
		$this->descr = $args["descr"];
		// Do the research ...
		$this->findRequired($idSetter, $keywords);
		// Do the database updates ...
		$idTest = $this->createTestRecord($idSetter);
		$this->addNewWords($idSetter);
		$this->addNewLinks($idTest);

		$database = array();
		$database[] = $this->listKeywords($idSetter);
		$database[] = $this->listTests($idSetter);
		$database[] = $this->listQuestions($idSetter, $idTest);
		return array
		(
			"database" => $database,
		);
	}

	public function updateTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$idSetter = $args["idSetter"];
		$idTest = $args["idTest"];
		$keywords = $args["keywords"];
		$this->ident = $args["ident"];
		$this->descr = $args["descr"];
		// Do the research ...
		$this->findCurrent($idTest);
		$this->findRequired($idSetter, $keywords);
		// Do the database updates ...
		$this->addNewWords($idSetter);
		$this->removeOldLinks($idTest);
		$this->addNewLinks($idTest);
		$this->updateTestRecord($idTest);

		$database = array();
		$database[] = $this->listKeywords($idSetter);
		$database[] = $this->listTests($idSetter);
		$database[] = $this->listQuestions($idSetter, $idTest);
		return array
		(
			"database" => $database,
		);
	}

	public function deleteTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$idSetter = $args["idSetter"];
		$idTest = $args["idTest"];
		$this->deleteTestRecord($idTest);

		$database = array();
		$database[] = $this->listTests($idSetter);
		return array
		(
			"database" => $database,
		);
	}

	public function createQuestion($args)
	{
		$this->dbx = Connection::makeDbx(false);
		// Gather arguments ...
		$idSetter = $args["idSetter"];
		$idTest = $args["idTest"];
		$ident = $args["ident"];
		$query = $args["query"];
		$reply = $args["reply"];
		$sequence = 1;			// temporary
		// Create new the database records ...
		$idQuestion = $this->createQuestionRecord($ident, $query);
		$this->createAnswerRecord($idQuestion, $reply);
		$this->createTestQuestionRecord($idTest, $idQuestion, $sequence);
		// Return the updated part of the database ...
		$database = array();
		$database[] = $this->listQuestions($idSetter, $idTest);
		return array
		(
			"database" => $database,
		);
	}

	public function deleteQuestion($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$idTest = $args["idTest"];
		$idQuestion = $args["idQuestion"];
		$this->deleteQuestionRecord($idQuestion);
		$database = array();
		$database[] = array
		(
			"action" => "delete",
			"table" => "question",
			"dbid" => $idQuestion,
			"parent" => $idTest,
		);
		return array
		(
			"database" => $database,
		);
	}

	// Trivially does the same as listSetters. Both will be updated in the future.
	// The hierarchy will be:
	// 1.	the logged on user (readLogged(): this one)
	// 2.	setters whose tests the user can edit, normally only the logged on user
	// 3. tests set by a setter
	// 4. questions within a test
	// Also, at level 3, are the keywords that the setter has applied
	private function readLogged($idLogged)
	{
		$sql = <<<END_SQL
SELECT idSetter AS idLogged, identifier AS ident
FROM setter
WHERE idSetter=:idLogged
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idLogged" => $idLogged,
		));
		$records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: array();
		return array
		(
			"action" => "list",
			"table" => "logged",
			"records" => $records,
		);
	}

	// See comment for readLogged.
	private function listSetters($idLogged)
	{
		$sql = <<<END_SQL
SELECT idSetter, identifier AS ident
FROM setter
WHERE idSetter=:idSetter
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $idLogged,
		));
		$records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: array();
		return array
		(
			"action" => "list",
			"table" => "setter",
			"records" => $records,
		);
	}

	private function listTests($idSetter)
	{
		$sql = <<<END_SQL
SELECT test.idTest AS idTest, identifier AS ident, descr, theWord,
DATE_FORMAT(test.added, "%Y-%m-%d") AS added
FROM (test
LEFT JOIN test_key
ON test.idTest=test_key.idTest)
LEFT JOIN keyword
ON test.idSetter=keyword.idSetter AND test_key.indexSetter=keyword.indexSetter
WHERE test.idSetter=:idSetter
ORDER BY test.added, keyword.theWord;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $idSetter,
		));
		$records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: array();

		// Transform the test records by creating potentially multiple references to
		// keywords in the same test record ...
		$repTest = 0;
		$count = count($records);
		for ($i = 0; $i < $count; ++$i)
		{
			$test = &$records[$i];
			$key = $test["theWord"];
			unset($test["theWord"]);
			if ($repTest && $test["idTest"] == $repTest["idTest"])
			{
				unset($records[$i]);                  // delete this row
				// ... this doesn't change the array indexes, but apparently, we don't care!
			}
			else
			{
				$repTest = &$test;
				$repTest["keywords"] = array();
			}
			$repTest["keywords"][strtolower($key)] = $key;
		}
		return array
		(
			"action" => "list",
			"table" => "test",
			"idSetter" => $idSetter,
			"records" => $records,
		);
	}

	private function listKeywords($idSetter)
	{
		// Get all the keywords ...
		$sql = <<<END_SQL
SELECT theWord
FROM keyword
WHERE idSetter=:idSetter
ORDER BY theWord;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $idSetter,
		));
		$words = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: array();

		// We now need to rearrange the keywords as an associative array ...
		$records = array();
		$count = count($words);
		for ($i = 0; $i < $count; ++$i)
		{
			$key = $words[$i]["theWord"];
			$records[strtolower($key)] = $key;
		}

		return array
		(
			"action" => "list",
			"table" => "keyword",
			"idSetter" => $idSetter,
			"records" => $records,
		);
	}

	private function listQuestions($idSetter, $idTest)
	{
		$sql = <<<END_SQL
SELECT question.idQuestion AS idQuestion, identifier AS ident,
question.query AS query, test_question.sequence AS sequence, answer.reply AS reply
FROM (question
INNER JOIN test_question
ON question.idQuestion=test_question.idQuestion)
INNER JOIN answer
ON question.idQuestion=answer.idQuestion
WHERE test_question.idTest=:idTest
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $idTest,
		));
		$records = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: array();

		return array
		(
			"action" => "list",
			"table" => "question",
			"idSetter" => $idSetter,
			"idTest" => $idTest,
			"records" => $records,
		);
	}

	private function findCurrent($idTest)
	{
		$sql = <<<END_SQL
SELECT indexSetter
FROM test_key
WHERE idTest=:idTest
ORDER BY indexSetter;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $idTest,
		));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row)
		{
			$this->curLinks[] = $row["indexSetter"];
		}
	}

	private function findRequired($idSetter, $keywords)
	{
		$sql = <<<END_SQL
SELECT theWord, indexSetter
FROM keyword
WHERE idSetter=:idSetter
ORDER BY indexSetter;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $idSetter,
		));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// If there are already some keywords, then take the next index, else start at 1 ...
		$count = count($rows);
		$freeIndex = $count > 0 ? $rows[$count - 1]["indexSetter"] + 1 : 1;
		foreach ($keywords as $theWord)
		{
			$found = false;
			foreach ($rows as $row)
			{
				if (strcasecmp($theWord, $row["theWord"]) == 0)
				{
					$this->reqLinks[] = $row["indexSetter"];
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				$this->newWords[$freeIndex] = $theWord;
				$this->reqLinks[] = $freeIndex++;
			}
		}
	}

	private function createTestRecord($idSetter)
	{
		$sql = <<<END_SQL
INSERT INTO test (idSetter, identifier, descr, added)
VALUES (:idSetter, :ident, :descr, now());
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $idSetter,
			":ident" => $this->ident,
			":descr" => $this->descr,
		));
		return $this->dbx->lastInsertId();
	}

	private function addNewWords($idSetter)
	{
		foreach ($this->newWords as $indexSetter => $theWord)
		{
			$sql = <<<END_SQL
INSERT INTO keyword (idSetter, indexSetter, theWord)
VALUES (:idSetter, :indexSetter, :theWord);
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idSetter" => $idSetter,
				":indexSetter" => $indexSetter,
				":theWord" => $theWord,
			));
		}
	}

	private function removeOldLinks($idTest)
	{
		foreach (array_diff($this->curLinks, $this->reqLinks) as $indexSetter)
		{
			$sql = <<<END_SQL
DELETE FROM test_key
WHERE idTest=:idTest AND indexSetter=:indexSetter;
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idTest" => $idTest,
				":indexSetter" => $indexSetter,
			));
		}
	}

	private function addNewLinks($idTest)
	{
		foreach (array_diff($this->reqLinks, $this->curLinks) as $indexSetter)
		{
			$sql = <<<END_SQL
INSERT INTO test_key (idTest, indexSetter)
VALUES (:idTest, :indexSetter);
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idTest" => $idTest,
				":indexSetter" => $indexSetter,
			));
		}
	}

	private function updateTestRecord($idTest)
	{
		$sql = <<<END_SQL
UPDATE test
SET identifier=:ident, descr=:descr
WHERE idTest=:idTest;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $idTest,
			":ident" => $this->ident,
			":descr" => $this->descr,
		));
	}

	private function deleteTestRecord($idTest)
	{
		$sql = <<<END_SQL
DELETE FROM test, test_key
USING test
LEFT JOIN test_key
ON test.idTest=test_key.idTest
WHERE test.idTest=:idTest;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $idTest,
		));
	}

	private function createQuestionRecord($ident, $query)
	{
		$sql = <<<END_SQL
INSERT INTO question (query, identifier)
VALUES (:query, :ident);
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":query" => $query,
			":ident" => $ident,
		));
		return $this->dbx->lastInsertId();
	}

	private function createAnswerRecord($idQuestion, $reply)
	{
		$sql = <<<END_SQL
INSERT INTO answer (idQuestion, reply)
VALUES (:idQuestion, :reply);
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idQuestion" => $idQuestion,
			":reply" => $reply,
		));
	}

	private function createTestQuestionRecord($idTest, $idQuestion, $sequence)
	{
		$sql = <<<END_SQL
INSERT INTO test_question (idTest, idQuestion, sequence)
VALUES (:idTest, :idQuestion, :sequence);
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $idTest,
			":idQuestion" => $idQuestion,
			":sequence" => $sequence,
		));
	}

	private function deleteQuestionRecord($idQuestion)
	{
		$sql = <<<END_SQL
DELETE FROM question
WHERE question.idQuestion=:idQuestion;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idQuestion" => $idQuestion,
		));		
	}
}
