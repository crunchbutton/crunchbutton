<?php

class Controller_Api_Marketing_Materials extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch (c::getPagePiece(4)) {
				case 'save':
					$this->_save();
					break;
				default:
					$this->_mailInfo();
					break;
			}
	}

	private function _save(){
		$refil = new Cockpit_Marketing_Materials_Refil;
		$admin = c::user();
		$refil->id_admin = $admin->id_admin;
		$refil->date = date('Y-m-d H:i:s');
		$refil->save();
		echo json_encode( ['success' => true] );exit;
	}

	private function _mailInfo(){
		$admin = c::user();
		$paymentType = $admin->payment_type();
		$out = [];
		$out['name'] = $admin->name;
		$out['phone'] = $admin->phone;
		$out['email'] = $admin->email;
		$out['address'] = $paymentType->address;
		echo json_encode( $out );exit;
	}
}
