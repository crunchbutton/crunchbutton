<?php

class Crunchbutton_Community_Resource_Community extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_resource_community')
			->idVar('id_community_resource_community')
			->load($id);
	}
	function removeByResource( $id_community_resource ){
		c::db()->query( 'DELETE FROM community_resource_community WHERE id_community_resource = "' . $id_community_resource . '"' );
	}

}

