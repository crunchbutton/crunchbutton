<?php

class Crunchbutton_Admin_Shift_Assign_Permanently extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_shift_assign_permanently')
			->idVar('id_admin_shift_assign_permanently')
			->load($id);
	}

	public function adminIsPermanently( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		$id_community_shift_father = $shift->recurringId();
		if( $id_community_shift_father ){
			$shift = Crunchbutton_Admin_Shift_Assign_Permanently::q( "SELECT * FROM admin_shift_assign_permanently WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift_father . " LIMIT 1" );
			if( $shift->id_admin_shift_assign_permanently ){
				return true;
			}
		}
		return false;
	}

	public function adminHasPermanentlyShift( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Admin_Shift_Assign_Permanently::q( "SELECT * FROM admin_shift_assign_permanently WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift . " LIMIT 1" );
		if( $shift->id_admin_shift_assign_permanently ){
			return true;
		}
		return false;
	}

	public function getByShift( $id_community_shift, $date = null ){

		$query = 'SELECT aap.* FROM admin_shift_assign_permanently aap
							INNER JOIN admin a ON aap.id_admin = a.id_admin AND a.active = true
							WHERE id_community_shift = "' . $id_community_shift . '"';
		$_perms = [];
		$perms = Crunchbutton_Admin_Shift_Assign_Permanently::q( $query );
		if( !$date ){
			return $perms;
		}
		foreach( $perms as $perm ){
			// Get the first assigned for this shift
			$shift = Crunchbutton_Community_Shift::q( 'SELECT cs.* FROM admin_shift_assign asa
																									INNER JOIN community_shift cs ON asa.id_community_shift = cs.id_community_shift
																									AND cs.id_community_shift_father = ? AND asa.id_admin = ?
																									ORDER by date_start ASC LIMIT 1', [ $perm->id_community_shift, $perm->id_admin ] )->get( 0 );
			if( $shift->id_community_shift ){
				$timezone = $shift->timezone();
				$date_base = DateTime::createFromFormat( 'Y-m-d H:i:s', $date . ' 00:00:00', new DateTimeZone( $timezone ) );
				if( $date_base->format( 'YmdHis' ) > $shift->dateStart()->format( 'YmdHis' )  ){
					$_perms[] = $perms;
				}
			}
		}
		return $_perms;
	}

	public function addDriver( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		$id_community_shift_father = $shift->recurringId();
		if( $id_community_shift_father ){
			$permanently = Crunchbutton_Admin_Shift_Assign_Permanently::q( 'SELECT * FROM admin_shift_assign_permanently WHERE id_community_shift = "' . $id_community_shift_father . '" AND id_admin = "' . $id_admin . '"'  );
			if( $permanently->id_admin_shift_assign_permanently ){
				return $permanently;
			} else {
				$permanently = new Crunchbutton_Admin_Shift_Assign_Permanently();
				$permanently->id_community_shift = $id_community_shift_father;
				$permanently->id_admin = $id_admin;
				$permanently->save();
				return $permanently;
			}
		}
	}

	public function removeByShift( $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		$id_community_shift_father = $shift->recurringId();
		if( $id_community_shift_father ){
			return c::dbWrite()->query( "DELETE FROM admin_shift_assign_permanently WHERE id_community_shift = " . $id_community_shift_father );
		}
	}

	public function removeByAdminShift( $id_admin, $id_community_shift ){
		$shift = Crunchbutton_Community_Shift::o( $id_community_shift );
		$id_community_shift_father = $shift->recurringId();
		if( $id_community_shift_father ){
			return c::dbWrite()->query( "DELETE FROM admin_shift_assign_permanently WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift_father );
		}
	}

	public function removeByAdminShiftFather( $id_admin, $id_community_shift ){
		return c::dbWrite()->query( "DELETE FROM admin_shift_assign_permanently WHERE id_admin = " . $id_admin . " AND id_community_shift = " . $id_community_shift );
	}

}
