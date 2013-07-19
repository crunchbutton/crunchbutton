<?php

class Controller_logs_content extends Crunchbutton_Controller_Account {
	public function init() {

		$search = [];
		if ($_REQUEST['limit']) {
			$search['limit'] = intval($_REQUEST['limit']);
		}

		if ($_REQUEST['search']) {
			if ($_REQUEST['search']{0} == '#') {
				$search['order'] = substr($_REQUEST['search'],1);
			} else {
				$search['search'] = $_REQUEST['search'];
			}
		}

		if ($_REQUEST['dates']) {
			$dates = explode(',',$_REQUEST['dates']);
			$search['start'] = $dates[0];
			$search['end'] = $dates[1];
		}

		if ($_REQUEST['type']) {
			$search['type'] = $_REQUEST['type'];
		}

		if ($_REQUEST['level']) {
			$search['level'] = $_REQUEST['level'];
		}

		c::view()->logs = Crunchbutton_Log::find($search);

		if ($_REQUEST['export']) {
			c::view()->layout('layout/csv');
			c::view()->display('logs/csv', ['display' => true, 'filter' => false]);
		} else {
			c::view()->layout('layout/ajax');
			c::view()->display('logs/content');
		}
	}
}