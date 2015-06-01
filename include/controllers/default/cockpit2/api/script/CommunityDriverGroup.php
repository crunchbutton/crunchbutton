<?php

// change community.driver_group to link with group.id_group #5359

class Controller_Api_Script_CommunityDriverGroup extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$errs = array( 'not' => array( 'no-group' => array(), 'dup-group' => array() ) );

		$communities = Crunchbutton_Community::q( 'SELECT * FROM community ORDER BY id_community DESC' );
		foreach( $communities as $community ){
			if( $community->driver_group ){
				$group = Crunchbutton_Group::q( 'SELECT * FROM `group` WHERE name = "' . $community->driver_group . '"' );
				if( $group->count() == 1 ){

					// save group stuff
					$group->id_community = $community->id_community;
					$group->type = Crunchbutton_Group::TYPE_DRIVER;
					$group->save();

					// save community stuff
					$community->id_driver_group = $group->id_group;
					$community->save();

				} else {
					$errs[ 'not' ][ 'dup-group' ][] = [ 'id_community' => $community->id_community, 'name' => $community->name, 'count' => $group->count() ];
				}
			} else {
				$errs[ 'not' ][ 'no-group' ][] = [ 'id_community' => $community->id_community, 'name' => $community->name ];
			}
		}
		echo json_encode( $errs );exit;
	}
}