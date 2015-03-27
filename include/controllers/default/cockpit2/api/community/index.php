<?php

class Controller_api_community extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		switch ($this->method()) {
			case 'get':

				$community = Community::permalink( c::getPagePiece(2) );

				if (!$community->id_community) {
					$community = Community::o( c::getPagePiece(2) );
				}

				if (!$community->id_community) {
					header('HTTP/1.0 404 Not Found');
					exit;
				}

				switch ( c::getPagePiece(3) ) {
					case 'aliases':
						$aliases = $community->aliases();
						$out = [];
						foreach( $aliases as $alias ){
							$out[] = $alias->exports();
						}
						echo json_encode( $out );exit;
						break;

					case 'closelog':
						$aliases = $community->forceCloseLog();
						$out = [];
						foreach( $aliases as $alias ){
							$out[] = $alias->exports();
						}
						echo json_encode( $out );exit;
						break;

					default:
						echo $community->json();
						exit();
						break;
				}

				break;

			case 'post':

				if (!c::admin()->permission()->check(['global'])) {
					header('HTTP/1.1 401 Unauthorized');
					exit;
				}


				switch ( c::getPagePiece(3) ) {

					// save aliases
					case 'aliases':

						$community = Community::permalink( c::getPagePiece(2) );

						if( !$community->id_community ){
							$this->_error();
						}

						switch ( c::getPagePiece(4) ) {

							case 'add':
								$alias = new Crunchbutton_Community_Alias;
								$alias->id_community = $this->request()[ 'id_community' ];
								$alias->alias = $this->request()[ 'alias' ];
								$alias->prep = $this->request()[ 'prep' ];
								$alias->name_alt = $this->request()[ 'name_alt' ];
								$alias->top = $this->request()[ 'top' ];
								$alias->sort = $this->request()[ 'sort' ];
								$alias->save();

								if( $alias->id_community_alias ){
									echo json_encode( $alias->exports() );exit;
								} else {
									$this->_error( 'Error adding alias, please try it again!' );
								}

								break;
							case 'remove':
								$alias = Crunchbutton_Community_Alias::o( $this->request()[ 'id_community_alias' ] );
								if( !$alias->id_community_alias ){
									$this->_error();
								} else {
									$alias->delete();
								}
								echo json_encode( [ 'success' => true ] );exit;
								break;
							default:
								$this->error();
								break;
						}
					break;

					// save a community
					default:

						// save a community
						$id_community = $this->request()[ 'id_community' ];
						$is_new = false;
						if( $id_community ){
							$community = Crunchbutton_Community::o( $id_community );
							if( !$community->id_community ){
								$community = new Crunchbutton_Community;
								$is_new = true;
							}
						} else {
							$community = new Crunchbutton_Community;
							$is_new = true;
						}

						if( $is_new ){
							$_community = Crunchbutton_Community::permalink( $this->request()[ 'permalink' ] );
							if( $_community->id_community ){
								$this->_error( 'Sorry, this permalink was already taken!' );
							}
							$community->driver_group = Crunchbutton_Group::driverGroupOfCommunity( $this->request()[ 'name' ] );
						} else {
							if( $community->permalink != $this->request()[ 'permalink' ] ){
								$_community = Crunchbutton_Community::permalink( $this->request()[ 'permalink' ] );
								if( $_community->id_community ){
									$this->_error( 'Sorry, this permalink was already taken!' );
								}
							}
						}

						$community->active = $this->request()[ 'active' ];
						$community->auto_close = $this->request()[ 'auto_close' ];
						$community->loc_lat = $this->request()[ 'loc_lat' ];
						$community->loc_lon = $this->request()[ 'loc_lon' ];
						$community->name = $this->request()[ 'name' ];
						$community->permalink = $this->request()[ 'permalink' ];
						$community->private = 0;
						$community->image = $this->request()[ 'image' ];
						$community->range = $this->request()[ 'range' ];
						$community->is_auto_closed = intval( $this->request()[ 'is_auto_closed' ] );
						$community->timezone = $this->request()[ 'timezone' ];
						$community->id_driver_restaurant = $this->request()[ 'id_driver_restaurant' ];

						if( intval( $this->request()[ 'close_all_restaurants' ] ) != intval( $community->close_all_restaurants ) ){
							$community->close_all_restaurants = intval( $this->request()[ 'close_all_restaurants' ] );
							if( $community->close_all_restaurants ){
								$community->close_all_restaurants_id_admin = intval( c::admin()->id_admin );
								$community->close_all_restaurants_date = date( 'Y-m-d H:i:s' );
							} else {
								$community->close_all_restaurants_id_admin = null;
								$community->close_all_restaurants_date =  null;
							}
						}
						if( $community->close_all_restaurants && $this->request()[ 'close_all_restaurants_note' ] ){
							$community->close_all_restaurants_note = $this->request()[ 'close_all_restaurants_note' ];
						} else {
							$community->close_all_restaurants_note = '';
						}

						if( intval( $this->request()[ 'close_3rd_party_delivery_restaurants' ] ) != intval( $community->close_3rd_party_delivery_restaurants ) ){
							$community->close_3rd_party_delivery_restaurants = intval( $this->request()[ 'close_3rd_party_delivery_restaurants' ] );
							if( $community->close_3rd_party_delivery_restaurants ){
								$community->close_3rd_party_delivery_restaurants_id_admin = intval( c::admin()->id_admin );
								$community->close_3rd_party_delivery_restaurants_date = date( 'Y-m-d H:i:s' );
							} else {
								$community->close_3rd_party_delivery_restaurants_id_admin = null;
								$community->close_3rd_party_delivery_restaurants_date = null;
							}

						}
						if( ( $community->close_3rd_party_delivery_restaurants || $community->is_auto_closed ) && $this->request()[ 'close_3rd_party_delivery_restaurants_note' ] ){
							$community->close_3rd_party_delivery_restaurants_note = $this->request()[ 'close_3rd_party_delivery_restaurants_note' ];
						} else {
							$community->close_3rd_party_delivery_restaurants_note = '';
						}

						if( $this->request()[ 'driver_restaurant_name' ] ){
							$community->driver_restaurant_name = $this->request()[ 'driver_restaurant_name' ];
						}

						$dont_warn_till = $this->request()[ 'dont_warn_till_fmt' ];
						if( $dont_warn_till && ( $community->close_all_restaurants || $community->close_3rd_party_delivery_restaurants ) ){
							$dont_warn_till = new DateTime( $dont_warn_till, new DateTimeZone( c::config()->timezone ) );
							$community->dont_warn_till = $dont_warn_till->format( 'Y-m-d H:i:s' );
						} else {
							$community->dont_warn_till = null;
						}

						if( intval( $this->request()[ 'dont_warn_till_enabled' ] ) == 0 ){
							$community->dont_warn_till = null;
						}



						$community->save();

						if( $community->id_community ){
							// force driver group creation
							Crunchbutton_Group::getDeliveryGroupByCommunity($community->driver_group);
							echo $community->json();
						} else {
							$this->_error( 'error' );
						}
						break;
				}
				break;
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}