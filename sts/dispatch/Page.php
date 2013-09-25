<?php
//
// Page.php: base class for all project HTML pages
// Project: STS - Specialised Test Setter
//

class Page
{
	// Default to no main form (tree or HTML) to be created ...
	protected $redirect = null;

	protected function analyse($content) { }
	protected function issueForm($content) { }

	public function execute()
	{
		$this->redirect = null;
		$content = Content::getInstance();
		$content->setBasicStyle();			// sets the default (non-js) stylesheet
		try
		{
			$this->analyse($content);
		}
		catch (PDOException $e)
		{
			$content->setFatal($e);
		}
		catch (Exception $e)
		{
			$content->setWarning($e->getMessage());
			if (State::formIsOutstanding())
			{
				$this->issueForm($content);
			}
		}

		if (!$this->redirect)
		{
			// Create the active menu ...
			State::getActiveInstance()->createMenu($content);
	
			// Build, and echo out, the assembled content.
			$content->buildPage();
		}
		return $this->redirect;
	}
}
