<?php

class Controller_api_metrics_postgre extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			echo "NOT AUTHO";
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		$r = c::app()->metricsDB()->query('select * from admin');
		echo "SOMETHING";
		foreach ($r as $o) {
			echo var_dump($o);
		}
		exit;


	}
}