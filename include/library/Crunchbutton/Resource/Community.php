<?php

class Crunchbutton_Resource_Community extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('resource_community')
			->idVar('id_resource_community')
			->load($id);
	}
	function removeByResource( $id_resource ){
		c::dbWrite()->query( 'DELETE FROM resource_community WHERE id_resource = "' . $id_resource . '"' );
	}

}

