<?php

class Controller_api_community_list extends Crunchbutton_Controller_Rest {
	public function init() {

		// @todo: move this method to /communities and pass it params

		switch ( $this->method() ) {

			case 'get':

				switch ( c::getPagePiece( 3 ) ) {
					case 'permalink':
						$communities = Crunchbutton_Community::active();
						$export = [];
						foreach( $communities as $community ){
							if( $community->name && $community->permalink ){
								$export[] = array( 'permalink' => $community->permalink, 'name' => $community->name, 'id_community' => $community->id_community );
							}
						}
						echo json_encode( $export );
						break;
					// Simple list returns just the name and id
					case 'simple':
					default:
						$communities = Crunchbutton_Community::active();
						$export = [];
						foreach( $communities as $community ){
							if( $community->name ){
								$export[] = array( 'id_community' => $community->id_community, 'name' => $community->name );
							}
						}
						echo json_encode( $export );
						break;
				}

			break;

			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}

	}
}