<?php

class Controller_Api_Script_PexCardRemoveFunds extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$cards = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard' );
		$total = 0;
		foreach( $cards as $card ){
			$pexcard = $card;
			if( !$card->isBusinessCard() && !$card->isTestCard() ){
				$driver = $card->admin();
				if( !$driver->isWorking() ){
					$card = $card->load_card_info();
					if( $card && $card->availableBalance && floatval( $card->availableBalance ) > 0 ){
						$amount = $card->availableBalance;
						echo $driver->name . "\nCard ID:" . $card->lastName . " : US$" . $amount;
						$amount = $amount * -1;
						echo "\n";
						echo "\n";
						$total = $total + $amount;
						$pexcard->addFunds( [ 'action' => Crunchbutton_Pexcard_Action::ACTION_SHIFT_FINISHED, 'id_admin_shift_assign' => null, 'amount' => $amount ] );
					}
				}
			}
		}
		echo "\n";
		echo "\n";
		echo "\n";
		echo $total;
	}
}