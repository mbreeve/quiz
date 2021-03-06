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

		// Get the logged-on data from the login state ...
		$logged = array
		(
			"idLogged" => StateLogin::getInstance()->idUser,
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
			"html" => $html,
			"logged" => $logged,
			"mods" => $mods,
		);
	}
}
