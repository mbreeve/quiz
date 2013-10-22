<?php
//
// QuestionsManager.php: called by QueryTests to create and update questions.
// Project: STS - Specialised Test Setter
//

class QuestionsManager
{
	private $dbx;
	private $idTest = 0;
	private $query = "";
	private $reply = "";

	public function readQuestions($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idTest = $args["idTest"];
		$this->getAllQuestions();

		// Return questions along with answers ...
		return array(
			//"request" => "readQuestions",
			//"idTest" => $this->idTest,
			"questions" => $this->questions,
		);
	}

	public function createQuestion($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idTest = $args["idTest"];
		$this->query = $args["query"];
		$this->reply = $args["reply"];
		// Do the database updates ...
		$this->createQuestionRecord();
		$this->createAnswerRecord();
		$this->createTestQuestionRecord();
		return array(
			//"request" => "createQuestion",
			"idTest" => $this->idTest,
			"idQuestion" => $this->idQuestion,
		);
	}

	public function updateQuestion($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idQuestion = $args["idQuestion"];
		$this->query = $args["query"];
		$this->reply = $args["reply"];
		// Do the database updates ...
		$this->updateQuestionRecord();
		return array(
			//"request" => "updateQuestion",
			"idQuestion" => $this->idQuestion,
		);
	}

/*
	public function deleteQuestion($args)
	{
		$this->dbx = Connection::makeDbx(false);
		$this->idQuestion = $args["idQuestion"];
		$this->deleteQuestionRecord();
		return array();
	}
*/

	private function getAllQuestions()
	{
		$sql = <<<END_SQL
SELECT question.idQuestion AS idQuestion,
sequence, query, correct, reply, optNum
FROM (test_question
INNER JOIN question
ON test_question.idQuestion=question.idQuestion)
INNER JOIN answer
ON question.idQuestion=answer.idQuestion
WHERE test_question.idTest=:idTest
ORDER BY sequence;
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idTest" => $this->idTest,
		));
		$this->questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function createQuestionRecord()
	{
		$sql = <<<END_SQL
INSERT INTO question (query)
VALUES (:query);
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":query" => $this->query,
		));
		$this->idQuestion = $this->dbx->lastInsertId();
	}

	private function createAnswerRecord()
	{
		$sql = <<<END_SQL
INSERT INTO answer (idQuestion, reply)
VALUES (:idQuestion, :reply);
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idQuestion" => $this->idQuestion,
			":reply" => $this->reply,
		));
	}

	private function createTestQuestionRecord()
	{
		$sql = <<<END_SQL
INSERT INTO test_question (idQuestion, idTest)
VALUES (:idQuestion, :idTest);
END_SQL;
		$stmt = $this->dbx->prepare($sql);
		$stmt->execute(array(
			":idQuestion" => $this->idQuestion,
			":idTest" => $this->idTest,
		));
	}

	private function updateQuestionRecord()
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

/*
	private function deleteQuestionRecord()
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
*/
}
