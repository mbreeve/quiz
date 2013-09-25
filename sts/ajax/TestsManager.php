<?php
//
// TestsManager.php: called by QueryTests to create and update tests.
// Project: STS - Specialised Test Setter
//

class TestsManager
{
	private $dbx;
	private $idSetter = 0;
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
		$this->idSetter = $args["idSetter"];
		$this->getAllTests();
		$this->transformTests();
		$this->getAllKeywords();
		$this->transformKeywords();

		// Return tests and keywords as separate branches ...
		return array(
			"request" => "readTests",
			"idSetter" => $this->idSetter,
			"tests" => $this->tests,
			"keywords" => $this->keywords,
		);
	}

	public function createTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idSetter = $args["idSetter"];
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
			"idSetter" => $this->idSetter,
			"idTest" => $this->idTest,
		);
	}

	public function updateTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idSetter = $args["idSetter"];
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
ON test.idSetter=keyword.idSetter AND test_key.indexSetter=keyword.indexSetter
WHERE test.idSetter=:idSetter
ORDER BY test.added, keyword.theWord;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $this->idSetter,
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
WHERE idSetter=:idSetter
ORDER BY theWord;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $this->idSetter,
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
SELECT indexSetter
FROM test_key
WHERE idTest=:idTest
ORDER BY indexSetter;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $this->idTest,
		));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row)
		{
			$this->curLinks[] = $row["indexSetter"];
		}
	}

	private function findRequired()
	{
		$sql = <<<END_SQL
SELECT theWord, indexSetter
FROM keyword
WHERE idSetter=:idSetter
ORDER BY indexSetter;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $this->idSetter,
		));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		// If there are already some keywords, then take the next index, else start at 1 ...
		$count = count($rows);
		$freeIndex = $count > 0 ? $rows[$count - 1]["indexSetter"] + 1 : 1;
		foreach ($this->keywords as $theWord)
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

	private function createTestRecord()
	{
		$sql = <<<END_SQL
INSERT INTO test (idSetter, name, descr, added)
VALUES (:idSetter, :name, :descr, now());
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idSetter" => $this->idSetter,
			":name" => $this->name,
			":descr" => $this->descr,
		));
		$this->idTest = $this->dbx->lastInsertId();
	}

	private function addNewWords()
	{
		foreach ($this->newWords as $indexSetter => $theWord)
		{
			$sql = <<<END_SQL
INSERT INTO keyword (idSetter, indexSetter, theWord)
VALUES (:idSetter, :indexSetter, :theWord);
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idSetter" => $this->idSetter,
				":indexSetter" => $indexSetter,
				":theWord" => $theWord,
			));
		}
	}

	private function removeOldLinks()
	{
		foreach (array_diff($this->curLinks, $this->reqLinks) as $indexSetter)
		{
			$sql = <<<END_SQL
DELETE FROM test_key
WHERE idTest=:idTest AND indexSetter=:indexSetter;
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idTest" => $this->idTest,
				":indexSetter" => $indexSetter,
			));
		}
	}

	private function addNewLinks()
	{
		foreach (array_diff($this->reqLinks, $this->curLinks) as $indexSetter)
		{
			$sql = <<<END_SQL
INSERT INTO test_key (idTest, indexSetter)
VALUES (:idTest, :indexSetter);
END_SQL;
			$stmt = $this->dbx->prepare($sql);
			$stmt->execute(array(
				":idTest" => $this->idTest,
				":indexSetter" => $indexSetter,
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
