<?php

class Controller_api_Giftcard extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {

			case 'post':
			case 'get':

				if ( c::admin()->id_admin ) {

					// Verify the permissions
					switch ( c::getPagePiece( 2 ) ) {
						case 'sms':
						case 'email':
						case 'bunchemail':
						case 'bunchsms':
						case 'generate':
							$ids_restaurant = $this->request()['id_restaurant'];
							foreach ( $ids_restaurant as $id_restaurant ) {
								if (!c::admin()->permission()->check( [ 'global','gift-card-all', 'gift-card-create-all', "gift-card-create-restaurant-{$id_restaurant}", "gift-card-restaurant-{$id_restaurant}"])) {
									return ;
								}
							}
							break;
						case 'relateuser':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if (!c::admin()->permission()->check( [ 'global','gift-card-all', 'gift-card-create-all', "gift-card-create-restaurant-{$giftcard->id_restaurant}", "gift-card-restaurant-{$giftcard->id_restaurant}"])) {
								return;
							}
							break;
						case 'delete':
						case 'removecredit':
								if (!c::admin()->permission()->check( [ 'global','gift-card-all', 'gift-card-delete'])) {
									return ;
								}
							break;
					}


					switch ( c::getPagePiece( 2 ) ) {

						case 'generate':
							$ids_restaurant = $this->request()['id_restaurant'];
							$ids_group = $this->request()['id_group'];
							$value = $this->request()['value'];
							$total = $this->request()['total'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
							$id_user = $this->request()['id_user'];
							$created_by = $this->request()['created_by'];
							$track = $this->request()['track'];
							$notify_phone = $this->request()['notify_phone'];
							$name = $this->request()['name'];
							$how_delivery = $this->request()['how_delivery'];
							$contact = $this->request()['contact'];
							$add_as_credit = $this->request()['add_as_credit'];
							$notify_by_email = $this->request()['notify_by_email'];
							$include_gift_card_id = $this->request()['include_gift_card_id'];
							$notify_by_sms = $this->request()['notify_by_sms'];
							$print = $this->request()['print'];
							$chars_to_use = $this->request()['chars_to_use'];
							$message = $this->request()['message'];
							$length = $this->request()['length'];
							$prefix = $this->request()['prefix'];

							// Store the ids
							$idIni = false;
							$idEnd = false;

							foreach( $ids_restaurant as $id_restaurant ){
								if( trim( $id_restaurant ) != '' ){

									for( $i = 1; $i<= $total; $i++) {
										$giftcard = new Crunchbutton_Promo;
										// id_restaurant == * means any restaurant
										if( $id_restaurant == '*' ){
											$giftcard->note = 'This gift is valid to any restaurant!' . "\n" . $note;
										} else {
											$giftcard->id_restaurant = $id_restaurant;
											$giftcard->note = $note;
										}
										$giftcard->value = $value;
										if( $id_user ){
											$giftcard->id_user = $id_user;
											$user = Crunchbutton_User::o( $id_user );
											$giftcard->phone =  $user->phone;
											if( $notify_by_email > 0 ){
												$giftcard->email = $user->email;
												$giftcard->email_subject = 'Congrats, you got a gift card';
												$giftcard->email_content = 'Congrats, you got a gift card to ' . Crunchbutton_Promo::TAG_RESTAURANT_NAME . '! To receive it, enter code: ' . Crunchbutton_Promo::TAG_GIFT_CODE . ' in your order notes or click here: ' . Crunchbutton_Promo::TAG_GIFT_URL . '.';
											}
										}
										$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
										$giftcard->note = $note;
										$giftcard->message = $message;

										$giftcard->track = $track;
										$giftcard->active = 1;
										$giftcard->created_by = $created_by;
										if( $track > 0 ){
											$giftcard->notify_phone = $notify_phone;
											$giftcard->name = $name;
											$giftcard->how_delivery = $how_delivery;
											$giftcard->contact = $contact;
										}
										$giftcard->id_order_reference = $id_order_reference;
										$giftcard->paid_by = $paid_by;
										if( $paid_by == 'other_restaurant' ){
											$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
										}
										$giftcard->date = date('Y-m-d H:i:s');
										$giftcard->save();

										if( $include_gift_card_id > 0 ){
											$giftcard->code = $giftcard->promoCodeGeneratorUseChars( $chars_to_use, $length, $giftcard->id_promo, $prefix );
										} else {
											$giftcard->code = $giftcard->promoCodeGeneratorUseChars( $chars_to_use, $length, '', $prefix );
										}

										if( $print ){
											$giftcard->issued = Crunchbutton_Promo::ISSUED_PRINT;
										}

										$giftcard->save();

										if( $ids_group ){
											foreach ( $ids_group as $id_group ) {
												$new = new Crunchbutton_Promo_Group_Promo();
												$new->id_promo = $giftcard->id_promo;
												$new->id_promo_group = intval( $id_group );
												$new->save();
											}
										}

										if( !$idIni ){
											$idIni = $giftcard->id_promo;
										}
										$idEnd = $giftcard->id_promo;

										if( $add_as_credit == '1' ){
											if( $id_user ){
												$giftcard->issued = Crunchbutton_Promo::ISSUED_CREDIT;
												$giftcard->addCredit( $id_user );
											}
										} else {
											if( $id_user ){
												if( $notify_by_email > 0 && $giftcard->email ){
													$giftcard->queNotifyEMAIL();
												}
												if( $notify_by_sms > 0 && $giftcard->phone ){
													$giftcard->queNotifySMS();
												}
											}
										}

										if($id_order_reference) {
											$order = Order::o($id_order_reference);
											if($order->id_order) {
												$support = $order->getSupport();
												if($support->id_support) {
													$support->addNote("Gift card issued #GIFT$giftcard->id_promo.", 'system', 'internal');
												}
											}
										}
									}
								}
							}
							echo json_encode(['success' => $idIni . '-' . $idEnd ]);
							break;
					case 'bunchsms':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$phones = $this->request()['phones'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];

							$created_by = $this->request()['created_by'];
							$track = $this->request()['track'];
							$notify_phone = $this->request()['notify_phone'];
							$name = $this->request()['name'];
							$how_delivery = $this->request()['how_delivery'];
							$contact = $this->request()['contact'];

							$phones = explode("\n", $phones);
							foreach ( $phones as $phone ) {
								if( trim( $phone ) != '' ){
									$giftcard = new Crunchbutton_Promo;
									// id_restaurant == * means any restaurant
									if( $id_restaurant == '*' ){
										$giftcard->note = 'This gift is valid to any restaurant!' . "\n" . $note;
									} else {
										$giftcard->id_restaurant = $id_restaurant;
										$giftcard->note = $note;
									}
									$giftcard->code = $giftcard->promoCodeGenerator();
									$giftcard->value = $value;
									$giftcard->phone = $phone;
									$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
									$giftcard->date = date('Y-m-d H:i:s');
									$giftcard->note = $note;
									$giftcard->track = $track;
									$giftcard->active = 1;
									$giftcard->created_by = $created_by;
									if( $track > 0 ){
										$giftcard->notify_phone = $notify_phone;
										$giftcard->name = $name;
										$giftcard->how_delivery = $how_delivery;
										$giftcard->contact = $contact;
									}
									$giftcard->id_order_reference = $id_order_reference;
									$giftcard->paid_by = $paid_by;
									if( $paid_by == 'other_restaurant' ){
										$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
									}
									$giftcard->save();
									$giftcard->queNotifySMS();
								}
							}
							echo json_encode(['success' => 'success']);
						break;
					case 'bunchemail':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$emails = $this->request()['emails'];
							$subject = $this->request()['subject'];
							$content = $this->request()['content'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];

							$created_by = $this->request()['created_by'];
							$track = $this->request()['track'];
							$notify_phone = $this->request()['notify_phone'];
							$name = $this->request()['name'];
							$how_delivery = $this->request()['how_delivery'];
							$contact = $this->request()['contact'];

							$emails = explode("\n", $emails);
							foreach ( $emails as $email ) {
								if( trim( $email ) != '' ){
									$giftcard = new Crunchbutton_Promo;
									// id_restaurant == * means any restaurant
									if( $id_restaurant == '*' ){
										$giftcard->note = 'This gift is valid to any restaurant!' . "\n" . $note;
									} else {
										$giftcard->id_restaurant = $id_restaurant;
										$giftcard->note = $note;
									}
									$giftcard->code = $giftcard->promoCodeGenerator();
									$giftcard->value = $value;
									$giftcard->email = $email;
									$giftcard->email_subject = $subject;
									$giftcard->email_content = $content;
									$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
									$giftcard->date = date('Y-m-d H:i:s');
									$giftcard->note = $note;
									$giftcard->track = $track;
									$giftcard->active = 1;
									$giftcard->created_by = $created_by;
									if( $track > 0 ){
										$giftcard->notify_phone = $notify_phone;
										$giftcard->name = $name;
										$giftcard->how_delivery = $how_delivery;
										$giftcard->contact = $contact;
									}
									$giftcard->id_order_reference = $id_order_reference;
									$giftcard->paid_by = $paid_by;
									if( $paid_by == 'other_restaurant' ){
										$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
									}
									$giftcard->save();
									$giftcard->queNotifyEMAIL();
								}
							}
							echo json_encode(['success' => 'success']);
						break;
					case 'relateuser':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( $giftcard->id_promo ){
								$giftcard->id_user =  $this->request()['id_user'];
								$giftcard->save();
								$giftcard->phone =  $giftcard->user()->phone;
								$giftcard->save();
								echo $giftcard->json();
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;
					case 'email':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( $giftcard->id_promo ){
								$giftcard->queNotifyEMAIL();
								echo $giftcard->json();
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;
					case 'sms':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( $giftcard->id_promo ){
								$giftcard->queNotifySMS();
								echo $giftcard->json();
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;
					case 'delete':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( !Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
								$giftcard->delete();
								echo json_encode(['success' => 'success']);
							} else {
								echo json_encode(['error' => 'already used']);
							}
							break;
					case 'removecredit':
							$giftcard = Crunchbutton_Promo::o( $this->request()['id_promo'] );
							if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
								$credit = $giftcard->credit();
								if( $credit->removeCreditLeft() ){
									echo json_encode(['success' => 'success']);
								} else {
									echo json_encode(['error' => 'error']);
								}
							} else {
								echo json_encode(['error' => 'error']);
							}
							break;

					}
				}

				if ( c::getPagePiece(2) == 'code' ) {
					// Get the giftcard (promo) by code
					$giftcard = Crunchbutton_Promo::byCode( $this->request()['code'] );
					// Check if the giftcard is valid
					if( $giftcard->id_promo ){
						// Check if the giftcard was already used
						if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
							echo json_encode(['error' => 'gift card already used']);
						} else {
							// It the gift has a user_id just this user will be able to use it
							if( $giftcard->id_user && $giftcard->id_user != c::user()->id_user ){
								echo json_encode(['error' => 'invalid gift card']);
								exit;
							}
							// Add credit to user
							$credit = $giftcard->addCredit( c::user()->id_user );
							if( $credit->id_credit ){
								if( $credit->id_restaurant ){
									echo json_encode( [ 'success' => [ 'value' => $credit->value, 'restaurant' => $credit->restaurant()->name, 'id_restaurant' => $credit->restaurant()->id_restaurant, 'permalink' => $credit->restaurant()->permalink ] ] );
								} else {
									echo json_encode( [ 'success' => [ 'value' => $credit->value ] ] );
								}
							} else {
								echo json_encode(['error' => 'gift card not added']);
							}
						}
					} else {
						echo json_encode(['error' => 'invalid gift card']);
					}
				}

				// Register url view
				if ( c::getPagePiece(2) == 'viewed' ) {
					$code = $this->request()['code'];
					// Get the giftcard (promo) by code
					$giftcard = Crunchbutton_Promo::byCode( $code);
					// Check if the giftcard is valid
					if( $giftcard->id_promo ){
						$giftcard->viewed = ( $giftcard->viewed ) ? $giftcard->viewed : 0;
						$giftcard->viewed++;
						$giftcard->save();
					}
				}

				if ( c::getPagePiece(2) == 'validate' ) {
					$code = $this->request()['code'];

					$valid = $this->valide_code( $code );
					if( $valid ){
						echo json_encode( $valid );
					} else {
						echo json_encode( [ 'error' => 'invalid gift card' ] );
					}
					exit();
				}

				if ( c::getPagePiece(2) == 'validate-words' ) {

					$words = $this->request()['words'];
					$phone = $this->request()['phone'];
					$id_restaurant = $this->request()['id_restaurant'];

					$words = preg_replace( "/(\r\n|\r|\n)+/", ' ', $words);
					$words = explode( ' ', $words );

					$words = array_unique( $words );

					foreach( $words as $word ){

						$word = trim( $word );

						if( $word == '' ){ continue; }
						$valid = $this->valide_code( $word, $phone, $id_restaurant );
						if( $valid && $valid[ 'success' ] ){
							echo json_encode( $valid );exit;
						}
					}
					echo json_encode( [ 'error' => 'invalid gift card' ] );
				}

			break;

			default:

				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}

	public function valide_code( $code, $phone = false, $id_restaurant = false ){

		$code = trim( $code );
		$code = str_replace( '"', '', $code );
		$code = str_replace( "'", '', $code );

		// At first check if it is an user's invite code - rewards: two way gift cards #2561
		$reward = new Crunchbutton_Reward;

		$valid = $reward->validateInviteCode( $code );
		$isInvite = $valid;

		if( $reward->checkIfItIsEligibleForFirstTimeOrder( $phone ) && $valid ){

			// check if there are points to add
			$add_points = $reward->getRefered();
			$delivery_free_points = $reward->pointsToGetDeliveryFree();

			if( $add_points == $delivery_free_points ){
				// we will show the same amount of the delivery fee #6966
				$restaurant = Restaurant::o( $id_restaurant );
				if( $restaurant->id_restaurant && $restaurant->delivery_fee ){
					return ( [ 'success' => [ 'value' => floatval( $restaurant->delivery_fee ), 'giftcard' => $code, 'message' =>  'Congrats, your delivery fee is on us! (for first time users only)' ] ] );
				}
			} else {
				$value = $reward->getReferredDiscountAmount();
				if( $value ){
					return ( [ 'success' => [ 'value' => floatval( $value ), 'giftcard' => $code, 'message' =>  'Congrats, your delivery fee is on us! (for first time users only)' ] ] );
				}
			}
		}

		if( !$reward->checkIfItIsEligibleForFirstTimeOrder( $phone ) && $valid ) {
			echo json_encode( ['error' => 'custom', 'warning' => 'Sorry, that discount is for first time users only' ] );exit;
		}

		// Get the giftcard (promo) by code
		$giftcard = Crunchbutton_Promo::byCode( $code )->get( 0 );
		// Check if the giftcard is valid
		if( $giftcard->id_promo ){

			$params = [];

			if( $phone ){
				$params[ 'phone' ] = $phone;
			}

			if( $id_restaurant ){
				$params[ 'id_restaurant' ] = $id_restaurant;
			}

			$discount_code = $giftcard->isDiscountCode( $params );
			if( $discount_code ){
				echo json_encode( $discount_code );exit;
			}

			// Check if the giftcard was already used
			if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
				return(['error' => 'gift card already used', 'giftcard' => $code ]);
			} else {
				// It the gift has a user_id just this user will be able to use it
				if( $giftcard->id_user && $giftcard->id_user != c::user()->id_user ){
					return(['error' => 'invalid gift card', 'giftcard' => $code ]);

				}
				return( [ 'success' => [ 'value' => $giftcard->value, 'id_restaurant' => $giftcard->id_restaurant, 'giftcard' => $code, 'restaurant' => $giftcard->restaurant()->name, 'permalink' => $giftcard->restaurant()->permalink ] ] );
			}
		} else {
			if( $isInvite ){
				return(['error' => 'invite not eligible', 'giftcard' => $code ] );
			} else {
				return(['error' => 'invalid gift card', 'giftcard' => $code ] );
			}
		}
		return false;
	}

}