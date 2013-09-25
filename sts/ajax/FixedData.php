<?php
//
// FixedData.php: called by QueryTests to do the javascript/ajax query: getFixedData.
// Project: STS - Specialised Test Setter
//

class FixedData
{
	public function getFixedData($args)
	{	
		// Get the element ids that will be used by jquery to create patches of html ...
		$html = array
		(
			"divBoard" => "divBoard",
			"divForm" => "divForm",
			"divMenu" => "divMenu",
		);

		// Get the owner data from the login state ...
		$owner = array
		(
			"idOwner" => StateLogin::getInstance()->idUser,
			"level" => StateLogin::getInstance()->level,
		);

		// Get the paths of the various javascript/jquery modules ...
		$modTestsGeneral = Globals::get("scripts") . "/";
		$mods = array
		(
			"TestsGeneral" => $modTestsGeneral,
		);

		// Put them all together for json
		return array(
			"request" => "getFixedData",
			"html" => $html,
			"owner" => $owner,
			"mods" => $mods,
		);
	}
}
