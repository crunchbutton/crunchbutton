<?php

class Crunchbutton_Community_Shift_Recursivity extends Cana_Table {

	const ACTION_IGNORE = 'ignore';

	public function __construct($id = null) {
		parent::__construct();
		$this->table('community_shift_recursivity')->idVar('id_community_shift_recursivity')->load($id);
	}

	public function addIgnore( $id_community_shift, $date ){
		$shift = Community_Shift::o( $id_community_shift );
		if( $shift->id_community_shift_father ){
			$id_community_shift = $shift->id_community_shift_father;
		}
		$recursivity = new Community_Shift_Recursivity;
		$recursivity->id_community_shift = $id_community_shift;
		$recursivity->date = $date;
		$recursivity->action = self::ACTION_IGNORE;
		$recursivity->save();
	}

	public function ignoreRecursivity( $id_community_shift, $date ){
		$ignore = Crunchbutton_Community_Shift_Recursivity::q( 'SELECT * FROM community_shift_recursivity WHERE id_community_shift = ? AND date = ? AND action = ? ORDER BY id_community_shift_recursivity DESC LIMIT 1 ', [ $id_community_shift, $date, self::ACTION_IGNORE ] )->get( 0 );
		if( $ignore->id_community_shift_recursivity ){
			return true;
		}
		return false;
	}


}