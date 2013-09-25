<?php
//
// State.php: base of the state machines
// Project: STS - Specialised Test Setter
//

// State is the base class to all the state machines. All state-machine data is
// accessed statically and is kept in the Session.
abstract class State
{
	// All the state macines handle their own menus ...
	abstract public function createMenu($content);

	// Each has a possible enter and leave (exit) function ...
	public function start() { }
	public function finish() { }

	// The active state machine is one of Login etc ...
	private static function getActiveMachine()
	{
		$machine = Session::get("stateActiveMachine", "StateLogin");
		return $machine;
	}

	public static function getActiveInstance()
	{
		$machine = self::getActiveMachine();
		if (!$machine)
		{
			$machine = "StateLogin";
			self::setActiveMachine($machine);
		}
		return StateLogin::getInstance();
	}

	public static function setActiveMachine($machine)
	{
		$old = self::getActiveMachine();
		if ($old != $machine)
		{
			// See above comment about not working under PHP ...
			StateLogin::getInstance()->finish();
			
			// Set the new machine ...
			Session::set("stateActiveMachine", $machine);

			// See above comment about not working under PHP ...
			StateLogin::getInstance()->start();
			self::closeForm();
		}
		
		// Yet again, see above comment about not working under PHP ...
		return StateLogin::getInstance();
	}

	public static function getPageBuilder()
	{
		return Session::get("statePageBuilder", "HomePage");
	}

	public static function setPageBuilder($new)
	{
		$old = Session::get("statePageBuilder", "HomePage");
		$method = $_SERVER["REQUEST_METHOD"];
		if ($new != $old)
		{
			Session::set("statePageBuilder", $new);
			self::closeForm();
		}
		else if ($_SERVER["REQUEST_METHOD"] != "POST")
		{
			self::closeForm();
		}
	}

	public static function shouldIssueForm()
	{
		if (!Session::get("stateFormIssued", false))
		{
			Session::set("stateFormIssued", true);
			Session::set("stateRefill", false);
			return true;
		}
		Session::set("stateRefill", true);
		return false;
	}

	public static function formIsOutstanding()
	{
		return Session::get("stateFormIssued", false);
	}

	public static function closeForm()
	{
		Session::set("stateFormIssued", false);
		Session::set("stateRefill", false);
	}
}
