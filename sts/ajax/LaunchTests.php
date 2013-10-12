<?php
//
// LaunchTests.php: manage (create/retrieve/update/delete) tests
// Project: STS - Specialised Test Setter
//

class LaunchTests extends Page
{
	protected function analyse($content)
	{
		// We will be using jquery ...
		Session::set("useJquery", true);

		// Set up info for when this HTML page is shown ...
		//$content->setGreeting("Manage Tests");

		// Add in the css/javascript/jquery references to the content page ...
		$style = Globals::get("style");
		$jquery = Globals::get("jquery");
		$scripts = Globals::get("scripts");

		// Set the following css includes as the content of the style div ...
		$content->styleHtml = <<<END_HTML
<div id='style'>
<link rel='stylesheet' href='$style/Layout.css' />
<link rel='stylesheet' href='$style/treetable.css' />
</div>
END_HTML;

		// Set the following js includes as the content of the scripts div ...
		$content->jsincHtml = <<<END_HTML
<div id='scripts'>
<script src='$jquery/jquery.js'></script>
<script src='$jquery/jquery.treetable.js'></script>
<script src='$scripts/TestsLauncher.js'></script>
<script src='$scripts/MainMenu.js'></script>
<script src='$scripts/WhiteBoard.js'></script>
<script src='$scripts/TestsRoot.js'></script>
<script src='$scripts/TreeBuilder.js'></script>
<script src='$scripts/TreeViewer.js'></script>
<script src='$scripts/TestEditor.js'></script>
<script src='$scripts/KeywordSelector.js'></script>
<script src='$scripts/QuestionsLister.js'></script>
<script src='$scripts/QuestionEditor.js'></script>
</div>
END_HTML;

		// Nothing more to be done ...
		return false;
	}
}
