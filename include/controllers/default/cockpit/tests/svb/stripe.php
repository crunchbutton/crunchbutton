<?php

class Controller_tests_svb_stripe extends Crunchbutton_Controller_Account {
	public function init() {
		switch (c::getPagePiece(3)) {
			case 'charge':

				$env = 'dev';
				Stripe::setApiKey(c::config()->stripe->{$env}->secret);
				$charge = Stripe_Charge::create([
					'amount' => 100,
					'currency' => 'usd',
					'card' => $_REQUEST['card'],
					'description' => 'Unit test charge'
				]);

				if ($charge->paid) {
					echo json_encode(['id' => $charge->id]);
				}
				
				break;

			default:
				c::view()->display('tests/svb/stripe', false);
				break;
		}

		exit;
	}
}