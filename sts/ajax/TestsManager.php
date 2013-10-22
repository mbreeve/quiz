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
	private $keywords = array();
	private $newWords = array();
	private $curLinks = array();
	private $reqLinks = array();

	public function loginSetter($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$idLogged = $args["idLogged"];
		$database = array();
		$setters = $this->getSetters($idLogged);
		$database[] = $setters;
		foreach ($setters["records"] as $setter)
		{
			$idSetter = $setter["idSetter"];
			$database[] = $this->getKeywords($idSetter);
			$database[] = $this->getTests($idSetter);
		}
		return array
		(
			"database" => $database,
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

		$database = array();
		$idSetter = $this->idSetter;
		$database[] = $this->getKeywords($idSetter);
		$database[] = $this->getTests($idSetter);
		return array
		(
			"database" => $database,
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

		$database = array();
		$idSetter = $this->idSetter;
		$database[] = $this->getKeywords($idSetter);
		$database[] = $this->getTests($idSetter);
		return array
		(
			"database" => $database,
		);
	}

	public function deleteTest($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idSetter = $args["idSetter"];
		$this->idTest = $args["idTest"];
		$this->deleteTestRecord();

		$database = array();
		$database[] = $this->getTests($this->idSetter);
		return array
		(
			"database" => $database,
		);
	}

	private function getSetters($idLogged)
	{
		$sql = <<<END_SQL
SELECT idSetter, setterName
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
			"pid" => 0,
			"records" => $records,
		);
	}

	private function getTests($idSetter)
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
			"pid" => $idSetter,
			"records" => $records,
		);
	}

	private function getKeywords($idSetter)
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
			"pid" => $idSetter,
			"records" => $records,
		);
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
