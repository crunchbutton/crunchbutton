<?php

class Controller_Admin_Credits_Content extends Crunchbutton_Controller_Account {

	public function init() {

		$search = [];
	
		if ($_REQUEST['limit']) {
			$search['limit'] = intval($_REQUEST['limit']);
		}
		
		if ($_REQUEST['id_user']) {
			$search['id_user'] = $_REQUEST['id_user'];
		}

		if ($_REQUEST['id_order']) {
			$search['id_order'] = $_REQUEST['id_order'];
		}

		if ($_REQUEST['type']) {
			$search['type'] = $_REQUEST['type'];
		}
		
		if ($_REQUEST['dates']) {
			$dates = explode(',',$_REQUEST['dates']);
			$search['start'] = $dates[0];
			$search['end'] = $dates[1];
		}
		
		if ($_REQUEST['restaurant']) {
			$search['restaurant'] = $_REQUEST['restaurant'];
		}

		c::view()->credits = Crunchbutton_Credit::find($search);
		c::view()->layout('layout/ajax');
		c::view()->display('admin/credits/content');
	}
}