<?php

class Controller_admin_save extends Crunchbutton_Controller_Rest {
	public function init() {
		$req = $this->request();
		$rsp = [];
		$rsp['result'] = 'OK';
		if($this->method() == 'get') {
			$rsp['result'] = 'error';
			$rsp['error'] = 'GET not supported';
		}
		if($this->method() == 'post') {
			try {
				$rsp['req'] = $req; // TODO this is not necessary
				switch($req['obj']) {
					case 'restaurant':
						$rsp['data'] = $this->save_restaurant($req[$req['obj']]);
						$rsp['msg'] = 'Saved.';
						break;
					default:
						$rsp['result'] = 'error';
						$rsp['error'] = 'Unimplemented object type.';
						break;
				}
			} catch (Exception $e) {
				$rsp['result'] = 'error';
				$rsp['error'] = 'Exception:' . $e->getMessage();
			}
		}
		echo json_encode($rsp);
	}

	private function save_restaurant($restaurant) {
		// a restaurant has a bunch of stuff in it
		if($restaurant['id_restaurant']) {
			$r = Restaurant::o($restaurant['id_restaurant']);
			$r->imports($restaurant);
			$r->save();
			return $r->exports();
		}
		return 'no id.';
	}

}


?>

