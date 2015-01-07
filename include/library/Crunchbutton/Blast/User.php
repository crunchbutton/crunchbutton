<?php

class Crunchbutton_Blast_User extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('blast_user')
			->idVar('id_blast_user')
			->load($id);
	}
	
	public function blast() {
		if (!isset($this->_blast)) {
			$this->_blast = Blast::o($this->id_blast);
		}
		return $this->_blast;
	}
	
	public function user() {
		if (!isset($this->_user)) {
			if (!$this->id_user) {
				$this->_user = User::byPhone($this->phone)->get(0);
			} else {
				$this->_user = User::o($this->id_user);
			}
		}
		return $this->_user;
	}
	
	public function name() {
		if (!isset($this->_name)) {
			if ($this->user()->id_user) {
				$this->_name = $this->user()->firstName();
			} else {
				$this->_name = 'Buddy';
			}
		}
		return $this->_name;
	}
	
	public function message() {
		if (!isset($this->_message)) {
			$this->_message = $this->blast()->content;

			foreach ($this->data() as $k => $v) {
				$this->_message = str_replace('%'.$k, $v, $this->_message);
			}
		}

		return $this->_message;
	}
	
	public function data() {
		if (!isset($this->_data)) {
			$this->_data = json_decode($this->data, true);
			$this->_data['phone'] = $this->_data['p'] = $this->phone;

			if (!$this->_data['n']) {
				$this->_data['n'] = $this->name();
			}
			if (!$this->_data['name']) {
				$this->_data['name'] = $this->name();
			}

			$this->_data['email'] = $this->_data['e'] = $this->email;
		}
		return $this->_data;
	}
	
	public function exports() {
		$data = $this->properties();
		$data['message'] = $this->message();
		return $data;
	}
}