<?php

class Controller_api_community extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':

				if( c::getPagePiece(2) == 'apply-list' ){

					$communities = Crunchbutton_Community::q( "SELECT c.name, c.permalink, c.id_community FROM community c where c.name NOT LIKE '%test%' and c.name != 'customer service' and c.name NOT LIKE '%duplication%' and c.active = 1 order by name asc" );
					$out = [];
					foreach( $communities as $community ){
						$community->name = ucwords( $community->name );
						$out[] = $community->properties();

					}
					echo json_encode( $out );
					exit;
				} else {
					$out = Community::o(c::getPagePiece(2));
					if (!$out->id_community) {
						$out = Community::permalink(c::getPagePiece(2));
					}
				}



				if ($out->id_community) {
					echo $out->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}

				break;
		}
	}
}