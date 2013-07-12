<?php

class Controller_tests_svb_balanced extends Crunchbutton_Controller_Account {
	public function init() {
		switch (c::getPagePiece(3)) {
			case 'charge':

				$env = 'dev';
				\Balanced\Settings::$api_key = c::config()->balanced->{$env}->secret;
				$b = Balanced\Marketplace::mine();

				$account = $b->createBuyer(
					'session-'.rand(1000,100000000).'@_DOMAIN_',
					$_REQUEST['card'],
					[
						'name' => 'UNIT TEST NAME',
						'phone' => '4150000000',
						'address' => ''
					]
				);
				$account->addCard($_REQUEST['card']);

				$charge = $account->debit(100, 'Crunchbutton', 'UNIT TEST');
				if ($charge->status == 'succeeded') {
					echo json_encode(['id' => $charge->id]);
				}
				
				break;

			default:
				c::view()->display('tests/svb/balanced', false);
				break;
		}

		exit;
	}
}