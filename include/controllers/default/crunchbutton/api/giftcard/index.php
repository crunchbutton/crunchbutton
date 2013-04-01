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
							$id_user = $this->request()['id_user'];
							for( $i = 1; $i<= $total; $i++){
								$giftcard = new Crunchbutton_Promo;
								$giftcard->id_restaurant = $id_restaurant;
								$giftcard->code = $giftcard->promoCodeGenerator();
								$giftcard->value = $value;
								if( $id_user ){
									$giftcard->id_user = $id_user;
									$user = Crunchbutton_User::o( $id_user );
									$giftcard->phone =  $user->phone;
									$giftcard->email =  $user->email;
								}
								$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
								$giftcard->date = date('Y-m-d H:i:s');
								$giftcard->save();
								if( $giftcard->email ){
									$giftcard->queNotifyEMAIL();
								} else if( $giftcard->phone ){
									$giftcard->queNotifySMS();
								}
							}
							echo json_encode(['success' => 'success']);
							break;
					case 'bunchsms':
							$id_restaurant = $this->request()['id_restaurant'];
							$value = $this->request()['value'];
							$phones = $this->request()['phones'];
							$phones = explode("\n", $phones);
							foreach ( $phones as $phone ) {
								if( trim( $phone ) != '' ){
									$giftcard = new Crunchbutton_Promo;
									$giftcard->id_restaurant = $id_restaurant;
									$giftcard->code = $giftcard->promoCodeGenerator();
									$giftcard->value = $value;
									$giftcard->phone = $phone;
									$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
									$giftcard->date = date('Y-m-d H:i:s');
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
							$emails = explode("\n", $emails);
							foreach ( $emails as $email ) {
								if( trim( $email ) != '' ){
									$giftcard = new Crunchbutton_Promo;
									$giftcard->id_restaurant = $id_restaurant;
									$giftcard->code = $giftcard->promoCodeGenerator();
									$giftcard->value = $value;
									$giftcard->email = $email;
									$giftcard->email_subject = $subject;
									$giftcard->email_content = $content;
									$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
									$giftcard->date = date('Y-m-d H:i:s');
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
							$credit = $giftcard->addCredit();
							if( $credit->id_credit ){
								echo json_encode( [ 'success' => [ 'value' => $credit->value, 'restaurant' => $credit->restaurant()->name, 'permalink' => $credit->restaurant()->permalink ] ] );
							} else {
								echo json_encode(['error' => 'gift card not added']);
							}
						}
					} else {
						echo json_encode(['error' => 'invalid gift card']);
					}
				}
 				
			break;
			default:
				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}
}