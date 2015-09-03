<?php

class Controller_api_community extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':

				if( c::getPagePiece(2) == 'apply-list' ){

					$communities = Crunchbutton_Community::q( 'SELECT
																ca.alias AS name, c.permalink, c.id_community, ca.prep, ca.name_alt, c.loc_lat, c.loc_lon, c.image
															FROM
																community_alias ca
															INNER JOIN community c ON c.id_community = ca.id_community 
															WHERE 
															c.name NOT LIKE "%test%"
															AND 
																c.name != "customer service"
															AND 
																c.name NOT LIKE "%duplication%"
															AND 
																c.active = 1
															ORDER BY ca.alias' );
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