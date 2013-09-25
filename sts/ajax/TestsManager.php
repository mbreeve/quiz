<?php
//
// TestsManager.php: called by QueryTests to create and update tests.
// Project: STS - Specialised Test Setter
//

class TestsManager
{
	private $dbx;
	private $idOwner = 0;
	private $idTest = 0;
	private $name = "";
	private $descr = "";
	private $tests = array();
	private $keywords = array();
	private $newWords = array();
	private $curLinks = array();
	private $reqLinks = array();

	public function readTests($args)
	{	
		$this->dbx = Connection::makeDbx(false);
		$this->idOwner = $args["idOwner"];
		$this->getAllTests();
		$this->transformTests();
		$this->getAllKeywords();
		$this->transformKeywords();

		// Return tests and keywords as separate branches ...
		return array(
			"request" => "readTests",
			"idOwner" => $this->idOwner,
			"tests" => $this->tests,
			"keywords" => $this->keywords,
		);
	}

	public function createTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idOwner = $args["idOwner"];
		$this->keywords = $args["keywords"];
		$this->name = $args["name"];
		$this->descr = $args["descr"];
		// Do the research ...
		$this->findRequired();
		// Do the database updates ...
		$this->createTestRecord();
		$this->addNewWords();
		$this->addNewLinks();
		return array(
			"request" => "createTest",
			"idOwner" => $this->idOwner,
			"idTest" => $this->idTest,
		);
	}

	public function updateTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idOwner = $args["idOwner"];
		$this->idTest = $args["idTest"];
		$this->keywords = $args["keywords"];
		$this->name = $args["name"];
		$this->descr = $args["descr"];
		// Do the research ...
		$this->findCurrent();
		$this->findRequired();
		// Do the database updates ...
		$this->addNewWords();
		$this->removeOldLinks();
		$this->addNewLinks();
		$this->updateTestRecord();
		return array(
			"request" => "updateTest",
			"idTest" => $this->idTest,
		);
	}

	public function deleteTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idTest = $args["idTest"];
		$this->deleteTestRecord();
		return array(
			"request" => "deleteTest",
			"idTest" => $this->idTest,
		);
	}

	private function getAllTests()
	{
		$sql = <<<END_SQL
SELECT test.idTest AS idTest, name, descr, theWord,
DATE_FORMAT(test.added, "%Y-%m-%d") AS added
FROM (test
LEFT JOIN test_key
ON test.idTest=test_key.idTest)
LEFT JOIN keyword
ON test.idOwner=keyword.idOwner AND test_key.indexOwner=keyword.indexOwner
WHERE test.idOwner=:idOwner
ORDER BY test.added, keyword.theWord;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idOwner" => $this->idOwner,
		));
		$this->tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function transformTests()
	{
		$repTest = 0;
		$count = count($this->tests);
		for ($i = 0; $i < $count; ++$i)
		{
			$test = &$this->tests[$i];
			$key = $test["theWord"];
			unset($test["theWord"]);
			if ($repTest && $test["idTest"] == $repTest["idTest"])
			{
				unset($this->tests[$i]);									// delete this row
				// ... this doesn't change the array indexes, but apparently, we don't care!
			}
			else
			{
				$repTest = &$test;
				$repTest["keywords"] = array();
			}
			$repTest["keywords"][strtolower($key)] = $key;
		}
	}

	private function getAllKeywords()
	{
		// Get all the keywords ...
		$sql = <<<END_SQL
SELECT theWord
FROM keyword
WHERE idOwner=:idOwner
ORDER BY theWord;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idOwner" => $this->idOwner,
		));
		$this->newWords = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function transformKeywords()
	{
		$count = count($this->newWords);
		for ($i = 0; $i < $count; ++$i)
		{
			$key = $this->newWords[$i]["theWord"];
			$this->keywords[strtolower($key)] = $key;
		}
	}

	private function findCurrent()
	{
		$sql = <<<END_SQL
SELECT indexOwner
FROM test_key
WHERE idTest=:idTest
ORDER BY indexOwner;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $this->idTest,
		));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row)
		{
			$this->curLinks[] = $row["indexOwner"];
		}
	}

	private function findRequired()
	{
		$sql = <<<END_SQL
SELECT theWord, indexOwner
FROM keyword
WHERE idOwner=:idOwner
ORDER BY indexOwner;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idOwner" => $this->idOwner,
		));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		// If there are already some keywords, then take the next index, else start at 1 ...
		$count = count($rows);
		$freeIndex = $count > 0 ? $rows[$count - 1]["indexOwner"] + 1 : 1;
		foreach ($this->keywords as $theWord)
		{
			$found = false;
			foreach ($rows as $row)
			{
				if (strcasecmp($theWord, $row["theWord"]) == 0)
				{
					$this->reqLinks[] = $row["indexOwner"];
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

	private function createTestRecord()
	{
		$sql = <<<END_SQL
INSERT INTO test (idOwner, name, descr, added)
VALUES (:idOwner, :name, :descr, now());
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idOwner" => $this->idOwner,
			":name" => $this->name,
			":descr" => $this->descr,
		));
		$this->idTest = $this->dbx->lastInsertId();
	}

	private function addNewWords()
	{
		foreach ($this->newWords as $indexOwner => $theWord)
		{
			$sql = <<<END_SQL
INSERT INTO keyword (idOwner, indexOwner, theWord)
VALUES (:idOwner, :indexOwner, :theWord);
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idOwner" => $this->idOwner,
				":indexOwner" => $indexOwner,
				":theWord" => $theWord,
			));
		}
	}

	private function removeOldLinks()
	{
		foreach (array_diff($this->curLinks, $this->reqLinks) as $indexOwner)
		{
			$sql = <<<END_SQL
DELETE FROM test_key
WHERE idTest=:idTest AND indexOwner=:indexOwner;
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idTest" => $this->idTest,
				":indexOwner" => $indexOwner,
			));
		}
	}

	private function addNewLinks()
	{
		foreach (array_diff($this->reqLinks, $this->curLinks) as $indexOwner)
		{
			$sql = <<<END_SQL
INSERT INTO test_key (idTest, indexOwner)
VALUES (:idTest, :indexOwner);
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idTest" => $this->idTest,
				":indexOwner" => $indexOwner,
			));
		}
	}

	private function updateTestRecord()
	{
		$sql = <<<END_SQL
UPDATE test
SET name=:name, descr=:descr
WHERE idTest=:idTest;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $this->idTest,
			":name" => $this->name,
			":descr" => $this->descr,
		));
	}

	private function deleteTestRecord()
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
			":idTest" => $this->idTest,
		));
	}
}
