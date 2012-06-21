<?php

class Controller_projects extends Cana_Controller {
	public function init() {
		Cana::view()->projects = Project::q('select * from project where active=1 limit 10');
		Cana::view()->display('projects/index');
	}
}