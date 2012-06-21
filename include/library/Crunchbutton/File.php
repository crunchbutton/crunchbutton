<?php

class Crunchbutton_File extends Cana_Table {
	public function notes() {
		if (!isset($this->_notes)) {
			$this->_notes = Note::q('
				SELECT note.* FROM note
				WHERE note.id_file="'.$this->id_file.'"
				AND note.active=1
			');
		}
		return $this->_notes;
	}
	
	public function exports() {
		$output = $this->properties();
		$output['url'] = $this->url();
		return $output;
	}
	
	public function server() {
		if (!isset($this->_server)) {
			$this->_server = Server::o($this->id_server);
		}
		return $this->_server;
	}
	
	public function url() {
		if (!isset($this->_url)) {
			$this->_url = $this->server()->url.$this->id_file.'/'.$this->name;
		}
		return $this->_url;
	}
	
	public function path() {
		if (!isset($this->_path)) {
			$filePath = explode('/',$this->path);
			array_pop($filePath);
			$this->_path = $this->server()->path.implode('/',$filePath).'/';
		}
		return $this->_path;
	}
	
	public function filename() {
		if (!isset($this->_filename)) {
			$filePath = explode('/',$this->path);
			$this->_filename = array_pop($filePath);
		}
		return $this->_filename;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('file')
			->idVar('id_file')
			->load($id);
	}
}