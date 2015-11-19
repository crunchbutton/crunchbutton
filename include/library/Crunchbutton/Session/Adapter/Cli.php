<?php

class Crunchbutton_Session_Adapter_Cli implements SessionHandlerInterface {

	public function read($id) {
		return '';
	}

	public function write($id, $data) {
		return true;
	}

	public function gc($m) {
		return true;
	}

	public function open($path, $name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function destroy($id) {
		return true;
	}

	public function generateAndSaveToken() {
		return true;
	}

	public function user() {
		return true;
	}

	public function save() {
		return false;
	}
}
