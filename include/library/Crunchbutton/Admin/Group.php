<?php

class Crunchbutton_Admin_Group extends Cana_Table_Trackchange {
	public function __construct($id = null) {
		parent::__construct();
		$this->changeOptions([
			'author_id' => 'id_author',
			'track_new' => true
		]);
		$this
			->table('admin_group')
			->idVar('id_admin_group')
			->load($id);
	}
	public function save($new = false){

		$group = Group::o( $this->id_group );
		if( $group->id_group ){
			$this->type = $group->getType();
		}

		Crunchbutton_Admin_Group_Log::create( [ 'id_admin_assigned' => $this->id_admin, 'id_group' => $this->id_group, 'assigned' => 1 ] );
		return parent::save();
	}
	public function delete() {
		Crunchbutton_Admin_Group_Log::create( [ 'id_admin_assigned' => $this->id_admin, 'id_group' => $this->id_group, 'assigned' => 0 ] );
		parent::delete();
	}
}
