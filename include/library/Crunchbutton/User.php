<?php

class Crunchbutton_User extends Cana_Table {
	public function watched() {
		return Project::q('
			SELECT project.* FROM project
			LEFT JOIN user_project on user_project.id_project=project.id_project
			WHERE user_project.id_user="'.$this->id_user.'"
		');
	}
	
	public function projects() {
	
	}
	
	public function password($password) {
		
	}
	
	public static function facebook($id) {
		return self::q('
			select user.* from user
			left join user_auth using(id_user)
			where
				user_auth.auth="'.Cana::db()->escape($id).'"
				and user_auth.`type`="facebook"
				and user.active=1
				and user_auth.active=1
			');
	}
	
	public function defaults() {
		return Restaurant_DefaultOrder::q('
			select * from restaurant_default_order where id_user="'.$this->id_user.'"
		');
	}
	
	public function exports() {
		$out = $this->properties();
		$out['defaults'] = $this->defaults();
		return $out;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user')
			->idVar('id_user')
			->load($id);
	}
}