<?php

class Controller_api_test extends Cana_Controller {


	public function e( $request ){
		if( $request->body ){
			echo json_encode( $request->body );exit;
		} else {
			echo '<pre>';var_dump( $request );exit();
		}
	}

	public function init(){



		$word = 'WUJ517YVR';

		// foreach( $words as $word ){
			$giftCardAdded = false;
			Log::debug([ 'totalOrdersByPhone' => $totalOrdersByPhone ]);
			// At first check if it is an user's invite code - rewards: two way gift cards #2561
			$reward = new Crunchbutton_Reward;
			$inviter = $reward->validateInviteCode( $word );
			if( $totalOrdersByPhone <= 1 && $inviter ){
				// get the value of the discount
				if( $inviter[ 'id_admin' ] ){
					$value = $reward->getReferredDiscountAmount();
					$admin_credit = $reward->adminRefersNewUserCreditAmount();
					$this->giftCardInviter = [ 'id_user' => $inviter[ 'id_user' ], 'id_admin' => $inviter[ 'id_admin' ], 'value' => $value, 'word' => $word, 'admin_credit' => $admin_credit ];
					if( $value ){
						$this->giftcardValue = $value;
						break;
					}
				} elseif( $inviter[ 'id_user' ] ){


					$credit = $reward->refersNewUserCreditAmount();
					echo '<pre>';var_dump( $settings );exit();

				}

			}
		// }

	}

}