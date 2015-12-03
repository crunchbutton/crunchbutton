<?php

class Controller_api_community extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401);
		}

		switch ( $this->method() ) {

			case 'get':

				switch ( c::getPagePiece( 2 ) ) {
					case 'by-alias':
						$community = Crunchbutton_Community_Alias::alias( c::getPagePiece( 3 ) );
						if( $community ){
							echo json_encode( $community );exit;
						} else {
							echo json_encode( [ 'error' => 'not found' ] );exit;
						}

						break;

					default:
						$community = Community::permalink( c::getPagePiece(2) );

						if (!$community->id_community) {
							$community = Community::o( c::getPagePiece(2) );
						}

						if (!$community->id_community) {
							$this->error(404);
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
								$log = $community->forceCloseLog( true, false, 60 );
								$out = [];
								foreach( $log as $closed ){
									$out[] = $closed->exports();
								}
								echo json_encode( $out );exit;
								break;

							case 'last-note':
								$note = $community->lastNote();;
								if( $note ){
									echo json_encode( $note->exports() );exit;
								}
								echo json_encode( [] );exit;
								break;

							default:

								switch ( c::getPagePiece( 3 ) ) {
									case 'open-close-status':
										$out = $community->properties();
										echo json_encode( $out );exit;
										break;

									case 'basic':
										$out = $community->properties();
										$out[ 'name_alt' ] = $community->name_alt();
										$out[ 'prep' ] = $community->prep();
										$out['type'] = $community->type();

										if( $out[ 'close_all_restaurants_id_admin' ] ){
											$admin = Admin::o( $out[ 'close_all_restaurants_id_admin' ] );
											$out[ 'close_all_restaurants_admin' ] = $admin->name;
											$date = new DateTime( $out[ 'close_all_restaurants_date' ], new DateTimeZone( c::config()->timezone ) );
											$out[ 'close_all_restaurants_date' ] = $date->format( 'M jS Y g:i:s A T' );
										}

										if( $out[ 'close_3rd_party_delivery_restaurants_id_admin' ] ){
											$admin = Admin::o( $out[ 'close_3rd_party_delivery_restaurants_id_admin' ] );
											$out[ 'close_3rd_party_delivery_restaurants_admin' ] = $admin->name;
											$date = new DateTime( $out[ 'close_3rd_party_delivery_restaurants_date' ], new DateTimeZone( c::config()->timezone ) );
											$out[ 'close_3rd_party_delivery_restaurants_date' ] = $date->format( 'M jS Y g:i:s A T' );
										}

										$next_sort = Crunchbutton_Community_Alias::q( 'SELECT MAX(sort) AS sort FROM community_alias WHERE id_community = ' . $community->id_community );
										if( $next_sort->sort ){
											$sort = $next_sort->sort + 1;
										} else {
											$sort = 1;
										}
										$out['next_sort'] = $sort;

										if( $out[ 'dont_warn_till' ] ){
											$out[ 'dont_warn_till' ] = [ 	'y' => $community->dontWarnTill()->format( 'Y' ), 'm' => $community->dontWarnTill()->format( 'm' ), 'd' => $community->dontWarnTill()->format( 'd' ), 'h' => $community->dontWarnTill()->format( 'H' ), 'i' => $community->dontWarnTill()->format( 'i' ) ];
											$out[ 'dont_warn_till_formated' ] = $community->dontWarnTill()->format( 'M jS Y g:i:s A T' );
											$out[ 'dont_warn_till_enabled' ] = true;
										} else {
											$out[ 'dont_warn_till' ] = null;
										}


										echo json_encode( $out );exit;
										break;

									default:
										echo $community->json();exit();
										break;
								}
								break;
						}
						break;
				}

				break;

			case 'post':

				if (!c::admin()->permission()->check(['global'])) {
					$this->error(401);
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
								$alias->alias = strtolower( $this->request()[ 'alias' ] );
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

					// save close/open
					case 'save-open-close':

						$status_changed = false;

						$id_community = $this->request()[ 'id_community' ];
						$community = Crunchbutton_Community::o( $id_community );
						$community->is_auto_closed = intval( $this->request()[ 'is_auto_closed' ] );

						if( intval( $this->request()[ 'close_all_restaurants' ] ) != intval( $community->close_all_restaurants ) ){
							$status_changed = true;
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
							$status_changed = true;
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

						if( $status_changed && $community->close_3rd_party_delivery_restaurants || $community->close_all_restaurants ){
							$reason = new Cockpit_Community_Closed_Reason;
							$reason->id_admin = c::user()->id_admin;
							$reason->id_community = $community->id_community;
							switch ( $this->request()[ 'reason' ] ) {
								case 'driver flaked':
									$reason->reason = 'driver flaked';
									$reason->id_driver = $this->request()[ 'reason_driver' ];;
									break;

								case 'other':
									$reason->reason = $this->request()[ 'reason_other' ];
									break;

								default:
									$reason->reason = $this->request()[ 'reason' ];
									break;
							}
							$reason->type = ( $community->close_all_restaurants ? Cockpit_Community_Closed_Reason::TYPE_ALL_RESTAURANTS : Cockpit_Community_Closed_Reason::TYPE_3RD_PARTY_DELIVERY_RESTAURANTS );
							$reason->date = date( 'Y-m-d H:i:s' );
							$reason->save();
						}

						if( $community->id_community ){
							echo $community->json();
						} else {
							$this->_error( 'error' );
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
						} else {
							if( $community->permalink != $this->request()[ 'permalink' ] ){
								$_community = Crunchbutton_Community::permalink( $this->request()[ 'permalink' ] );
								if( $_community->id_community ){
									$this->_error( 'Sorry, this permalink was already taken!' );
								}
							}
						}

						$community->tagline1 = $this->request()[ 'tagline1' ];
						$community->tagline2 = $this->request()[ 'tagline2' ];
						$community->active = $this->request()[ 'active' ];
						$community->driver_checkin = $this->request()[ 'driver_checkin' ];
						$community->combine_restaurant_driver_hours = $this->request()[ 'combine_restaurant_driver_hours' ];
						$community->drivers_can_open = $this->request()[ 'drivers_can_open' ];
						if( $is_new ){
							$community->automatic_driver_restaurant_name = 1;
						} else {
							$community->automatic_driver_restaurant_name = $this->request()[ 'automatic_driver_restaurant_name' ];
						}
						$community->auto_close = $this->request()[ 'auto_close' ];
						$community->loc_lat = $this->request()[ 'loc_lat' ];
						$community->loc_lon = $this->request()[ 'loc_lon' ];
						$community->name = $this->request()[ 'name' ];
						$community->permalink = $this->request()[ 'permalink' ];
						$community->auto_close_predefined_message = $this->request()[ 'auto_close_predefined_message' ];
						$community->private = 0;
						$community->image = $this->request()[ 'image' ];
						$community->range = $this->request()[ 'range' ];
						$community->timezone = $this->request()[ 'timezone' ];
						$community->id_driver_restaurant = $this->request()[ 'id_driver_restaurant' ];
						$community->amount_per_order = $this->request()[ 'amount_per_order' ];
						$community->campus_cash = $this->request()[ 'campus_cash' ];
						$community->campus_cash_name = $this->request()[ 'campus_cash_name' ];
						// feature disabled
						$community->campus_cash_fee = 0;
						$community->campus_cash_mask = $this->request()[ 'campus_cash_mask' ];
						$community->signature = $this->request()[ 'signature' ];
						$community->campus_cash_delivery_confirmation = $this->request()[ 'campus_cash_delivery_confirmation' ];
						$community->campus_cash_validation = $this->request()[ 'campus_cash_validation' ];
						$community->campus_cash_receipt_info = $this->request()[ 'campus_cash_receipt_info' ];
						$community->campus_cash_default_payment = $this->request()[ 'campus_cash_default_payment' ];

						$community->save();

						if( $community->id_community ){
							// force driver group creation
							if( !$community->id_driver_group ){
								$group = $community->groupOfDrivers();
							}
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