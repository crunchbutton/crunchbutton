<?php

class Controller_addme extends Cana_Controller {
	public function init() {
		header('Location: /owners');
		exit;
	}
}
