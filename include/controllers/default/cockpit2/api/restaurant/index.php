<?php

class Controller_api_restaurant extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$restaurant = Restaurant::permalink(c::getPagePiece(2));

		if (!$restaurant->id_restaurant) {
			$restaurant = Restaurant::o((int)c::getPagePiece(2));
		}

		if (!$restaurant->id_restaurant) {
			$this->error(404, true);
		}

		if (!c::admin()->permission()->check(['global', 'restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-edit', 'restaurant-'.$restaurant->id_restaurant.'-all', 'community-director'])) {
			$this->error(401, true);
		}

		if(c::admin()->isCommunityDirector()){
			$community = c::admin()->communityDirectorCommunity();
			if($community->id_community != $restaurant->community()->id_community){
				$this->error(401, true);
			};
		}

		$this->restaurant = $restaurant;

		switch (c::getPagePiece(3)) {
			case 'image':
				$this->_image();
				break;
			case 'force-close-status':
				$this->_forceCloseStatus();
				break;
			case 'save-force-close':
				$this->_saveForceClose();
				break;
			case 's3':
				$this->_s3();
				break;
			case 's3all':
				$this->_s3all();
				break;
			default:
				$this->_restaurant();
				break;

		}
	}


	private function _saveForceClose(){
		$this->restaurant->open_for_business = $this->request()[ 'open_for_business' ];
		if( $this->restaurant->open_for_business ){
			$this->restaurant->force_close_tagline = '';
		} else {
			$this->restaurant->force_close_tagline = $this->request()[ 'force_close_tagline' ];
		}
		$this->restaurant->save();
		echo json_encode( [ 'success' => true ] );exit;
	}

	private function _forceCloseStatus(){
		$out = [];
		$out[ 'id_restaurant' ] = $this->restaurant->id_restaurant;
		$out[ 'permalink' ] = $this->restaurant->permalink;
		$out[ 'name' ] = $this->restaurant->name;
		$out[ 'open_for_business' ] = $this->restaurant->open_for_business;
		$out[ 'force_close_tagline' ] = $this->restaurant->force_close_tagline;
		echo json_encode( $out );exit;
	}

	private function _s3all() {
		if (!c::admin()->permission()->check(['global'])) {
			$this->error(401, true);
		}


		$restaurants = Crunchbutton_Restaurant::q('
			select * from restaurant where `image` is not null
		');

		foreach ($restaurants as $restaurant) {
			//if ($resource->file != $resource->s3base()) {
				echo 'uploading '.$restaurant->name."\n";
				$s = $restaurant->updateImage();
				var_dump($s);
				echo "\n\n";
			//}
		}
	}

	private function _s3() {
		if (!c::admin()->permission()->check(['global'])) {
			$this->error(401, true);
		}

		$r = $this->restaurant->updateImage();
		var_dump($r);
	}

	private function _image() {
		// pull path of a temporary file

		switch ($this->method()) {
			case 'get':
				die($this->restaurant->image());
				header('Location: '.$this->restaurant->image());
				break;

			case 'post':
			case 'put':
				if ($_FILES) {
					foreach ($_FILES as $file) {
						$this->restaurant->updateImage($file['tmp_name'], $file['name']);
					}
				}
				break;
		}
	}

	private function _restaurant() {
		switch ($this->method()) {
			case 'get':
				echo $this->restaurant->json();
				break;
			case 'post':
				// do nothing for now
				break;
		}
	}
}