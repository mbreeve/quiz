<?php
//
// MimicEmail.php: describe the content of an email that would have been sent
// Project: STS - Specialised Test Setter
//

class MimicEmail extends Page
{
	private $emailDescr;

	protected function analyse($content)
	{
		// This page can build HTML, but only if there is a description.
		$this->emailDescr = Session::transfer("emailDescr");

		// There is possibly content (but no form) to be created ...
		if (!empty($this->emailDescr))
		{
			// Set up this HTML as the main form/content ...
			$content->formHtml = $this->outHtml();
		}

		// Nothing more to be done ...
		return false;
	}

	public function outHtml()
	{
		// The required description has been prepared and passed in the session.
		return nl2br(htmlspecialchars($this->emailDescr, ENT_NOQUOTES));
	}
}
