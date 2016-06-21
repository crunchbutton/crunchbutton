<?php

class Controller_api_fax extends Crunchbutton_Controller_RestAccount {
	public function init() {

		switch (c::getPagePiece(2)) {
			case 'upload':
				return $this->_upload();
				break;

			default:
				if($this->method() == 'post'){
					$this->_send();
				}
				break;
		}
	}

	private function _send(){
		$id_restaurant = $this->request()[ 'id_restaurant' ];
		$fax = $this->request()[ 'fax' ];
		$file = $this->request()[ 'file' ];
		if($fax && $file){
			$fax = Cockpit_Fax::create(['id_restaurant'=> $id_restaurant, 'file'=> $file, 'fax'=> $fax ]);
			$res = $fax->send();
			echo json_encode($res);exit;
		}
	}

	private function _upload(){
		if( $_FILES ){
			$ext = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
			if( Util::allowedExtensionUpload( $ext ) ){
				$file = $_FILES['file']['tmp_name'];
				$file = Cockpit_Fax::upload($file, $ext);
				if($file){
					echo json_encode(['success' => $file]);exit;
				}
				exit;
			} else {
				echo json_encode( ['error' => 'invalid extension'] );exit;
			}
		} else {
			echo json_encode( ['error' => 'error uploading file'] );exit;
		}
	}


}