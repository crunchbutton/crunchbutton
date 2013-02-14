<?php

class Controller_api_TimeMachine extends Crunchbutton_Controller_Rest
{
	public function set()
	{
		$return = null;
		$timezone = (isset($_GET['timezone'])) ? $_GET['timezone'] : 'America/New_York';
		if (!isset($_GET['time'])) $return = ['error' => '$_GET[\'time\'] was not set'];

		$DeLorean = new TimeMachine($timezone);
		$DeLorean->travel($_GET['time']);
		$DeLorean->toBeContinued();

		$return = ['time' => $DeLorean->now()];

		return $return;
	}

	public function reset()
	{
		$return = null;
		$timezone = (isset($_GET['timezone'])) ? $_GET['timezone'] : 'America/New_York';

		$DeLorean = new TimeMachine($timezone);
		$DeLorean->backToThePresent();
		$DeLorean->toBeContinued();

		$return = ['time' => $DeLorean->now()];

		return $return;
	}

	/**
	 * Calls the public methodds as actions
	 */
	public function init() {
		$action = c::getPagePiece(2);
		if (is_callable(array($this, $action)) && ($action[0] != '_')) {
			$return = $this->$action();
		} else {
			$return = ['error' => 'invalid request'];
		}
		echo json_encode($return);
	}
}
