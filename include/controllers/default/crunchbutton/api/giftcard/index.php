<?php

class Controller_api_Giftcard extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			
			case 'post':
				
				if ($_SESSION['admin']) {
					switch ( c::getPagePiece( 2 ) ) {
						case 'generate':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$total = $this->request()['total'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
							$id_user = $this->request()['id_user'];
							for( $i = 1; $i<= $total; $i++){
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
								if( $id_user ){
									$giftcard->id_user = $id_user;
									$user = Crunchbutton_User::o( $id_user );
									$giftcard->phone =  $user->phone;
								}
								$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
								$giftcard->note = $note;
								$giftcard->id_order_reference = $id_order_reference;
								$giftcard->paid_by = $paid_by;
								if( $paid_by == 'other_restaurant' ){
									$giftcard->id_restaurant_paid_by = $id_restaurant_paid_by;
								}
								$giftcard->date = date('Y-m-d H:i:s');
								$giftcard->save();
								if( $giftcard->phone ){
									$giftcard->queNotifySMS();
								}
							}
							echo json_encode(['success' => 'success']);
							break;
					case 'bunchsms':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$phones = $this->request()['phones'];
							$note = $this->request()['note'];
							$id_order_reference = $this->request()['id_order_reference'];
							$paid_by = $this->request()['paid_by'];
							$id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
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
									echo json_encode( [ 'success' => [ 'value' => $credit->value, 'restaurant' => $credit->restaurant()->name, 'id_restaurant' => $credit->restaurant()->id_restaurant ] ] );	
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
 				
				if ( c::getPagePiece(2) == 'validate' ) {

					$code = $this->request()['code'];
					// Get the giftcard (promo) by code
					$giftcard = Crunchbutton_Promo::byCode( $code);
					// Check if the giftcard is valid
					if( $giftcard->id_promo ){
						// Check if the giftcard was already used
						if( Crunchbutton_Promo::giftWasAlreadyUsed( $giftcard->id_promo ) ){
							echo json_encode(['error' => 'gift card already used', 'giftcard' => $code ]);
						} else {
							// It the gift has a user_id just this user will be able to use it
							if( $giftcard->id_user && $giftcard->id_user != c::user()->id_user ){
								echo json_encode(['error' => 'invalid gift card', 'giftcard' => $code ]);
								exit;		
							}
							echo json_encode( [ 'success' => [ 'value' => $giftcard->value, 'id_restaurant' => $giftcard->id_restaurant, 'giftcard' => $code ] ] );
						}
					} else {
						echo json_encode(['error' => 'invalid gift card', 'giftcard' => $code ] );
					}
				}

			break;
			default:
				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}
}