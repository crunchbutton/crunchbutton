<?php

class Controller_suggestions_content extends Crunchbutton_Controller_Account {

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
		
		if ($_REQUEST['type']) {
			$search['type'] = $_REQUEST['type'];
		}
		
		if ($_REQUEST['status']) {
			$search['status'] = $_REQUEST['status'];
		}
		
		if ($_REQUEST['dates']) {
			$dates = explode(',',$_REQUEST['dates']);
			$search['start'] = $dates[0];
			$search['end'] = $dates[1];
		}
		
		if ($_REQUEST['restaurant']) {
			$search['restaurant'] = $_REQUEST['restaurant'];
		}
		
		if ($_REQUEST['community']) {
			$search['community'] = $_REQUEST['community'];
		}

		c::view()->suggestions = Suggestion::find($search);
		c::view()->layout('layout/ajax');
		c::view()->display('suggestions/content');
	}
}