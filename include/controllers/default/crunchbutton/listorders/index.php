<?php

class Controller_listorders extends Crunchbutton_Controller_Account {
	public function init() {
		header('Location: /admin/orders');
		exit;
	}
}