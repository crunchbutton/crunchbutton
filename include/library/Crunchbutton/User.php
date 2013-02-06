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
	
	public function presets() {
		if (!isset($this->_presets)) {
			$this->_presets = Preset::q('
				select * from preset where id_user="'.$this->id_user.'"
			');
		}
		return $this->_presets;
	}
	
	public function preset($id_restaurant) {
		foreach ($this->presets() as $preset) {
			if ($preset->id_restaurant == $id_restaurant) {
				return $preset;
			}
		}
		return false;
	}
	
	public function exports() {
		$out = $this->properties();
		$out[ 'last_tip' ] = Order::lastTip( $this->id_user );
		$out[ 'facebook' ] = User_Auth::userHasFacebookAuth( $this->id_user );
		foreach ($this->presets() as $preset) {
			$out['presets'][$preset->id_restaurant] = $preset->exports();
		}
		
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