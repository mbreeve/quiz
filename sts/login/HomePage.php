<?php
//
// HomePage.php: defines the content of the project home page
// Project: STS - Specialised Test Setter
//

class HomePage extends Page
{
	protected function analyse($content)
	{
		// If we are here, we are no longer doing tests or using jquery ...
		State::setActiveMachine("StateLogin");
		Session::set("useJquery", false);

		// Set up this HTML as the main form/content ...
		//$content->formMarkup->setHtml("");

		// Set up info for when this HTML page is shown ...
		$content->setGreeting("Home Page");

		// Nothing more to be done ...
		return false;
	}
}
