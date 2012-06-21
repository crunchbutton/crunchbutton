<?php

class Controller_data extends Cana_Controller {   
	public function init() {
		$page = Cana::app()->pages();
		$file = new Crunchbutton_File($page[1]);

		if (!$file->id_file) {
			header('Status: 404 Not Found');
			exit;
		}

		$download = new Crunchbutton_Download([
			'file' => $file->filename(),
			'path' => $file->path(),
			'name' => $file->name
		]);

		$download->get(true);

	}
}