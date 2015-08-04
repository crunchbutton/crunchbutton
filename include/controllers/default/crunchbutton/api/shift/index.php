<?php

class Controller_api_shift extends Crunchbutton_Controller_Rest {

	public function init() {

		$id_admin_shift_assign = c::getPagePiece( 2 );

		$assignment = Crunchbutton_Admin_Shift_Assign::o( $id_admin_shift_assign );

		if( $assignment->id_admin_shift_assign ){

			switch ( c::getPagePiece( 3 ) ) {
				case 'shift-confirmed':
					$this->shiftConfirmed( $assignment );
					break;

				default:
					$this->firstCall( $assignment );
					break;
			}
		}
	}

	public function shiftConfirmed( $assignment ){
		if( $this->request()[ 'Digits' ] == '1' ){
			header('Content-type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n";
			echo '<Say voice="'.c::config()->twilio->voice.'">Shift confirmed. Thank you.</Say>';
			echo '<Pause length="1" />';
			echo '</Response>';
			Crunchbutton_Admin_Shift_Assign_Confirmation::confirm( $assignment );
		} else {
			$this->firstCall( $assignment );
		}
	}

	public function firstCall( $assignment ){
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?><Response>'."\n";
		echo '<Gather action="/api/shift/'.$assignment->id_admin_shift_assign.'/shift-confirmed" numDigits="1" timeout="10" finishOnKey="#" method="get">';
		echo '<Pause length="1"/>';
		echo '<Say voice="'.c::config()->twilio->voice.'">' . Crunchbutton_Admin_Shift_Assign_Confirmation::message( $assignment, Crunchbutton_Admin_Shift_Assign_Confirmation::TYPE_CALL ) . '</Say>';
		echo '</Gather>';
		echo '</Response>';
		exit;
	}

}