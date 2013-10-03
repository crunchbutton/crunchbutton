<?php

class Crunchbutton_Charge_Balanced extends Cana_Model {
	public function __construct($params = []) {
		if ($params['balanced_id']) {
			$account = Crunchbutton_Balanced_Account::byId($params['balanced_id']);
		}
		if (!$account->id) {
			$account = Crunchbutton_Balanced_Account::bySession();
		}
		
		if ($account->id) {
			$this->_customer = $account;
		}
	}
	
	public function charge($params = []) {

		$success = false;
		$reason = false;
		
		// if there is any card information provided, charge with it
		if ($params['card']) {
			$reason = true;
			try {
				// building the uri is faster than building the object since we dont have to go to the server again
				$card = c::balanced()->cards->uri.'/'.$params['card']['id'];

				if (!$this->customer()) {
					$customer = c::balanced()->createBuyer(
						'session-'.c::auth()->session()->id_session.'@_DOMAIN_',
						$card,
						[
							'name' => $params['name'],
							'phone' => $params['phone'],
							'address' => $params['address']
						]
					);
					$this->_customer = $customer;

				} else {
					$this->customer()->addCard($card);
				}

				$c = $this->customer()->debit($params['amount'] * 100, 'Crunchbutton', $params['restaurant']->name);

			} catch (Exception $e) {
				Log::debug( [ 'card error' => 'balanced', 'Exception' => $e->description, 'type' => 'card error' ]);
				$errors[] = 'Your card was declined. Please try again!';
				// $e->description
				$success = false;
			}
			if ($c->id) {
				$success = true;
				$txn = $c->id;
			}
		}

		if ($success) {
			// if the transaction was a success, create the token
			//$params['card'] = substr($params['number'], -4);
		}
		
		// if there was no number, and there was a user with a stored card, use the users stored card
		if (!$params['card'] && $params['user'] && $this->customer()->id) {

			$reason = true;
			try {
				$c = $this->customer()->debit($params['amount'] * 100, 'Crunchbutton', $params['restaurant']->name);

			} catch (Exception $e) {
				Log::debug( [ 'card error' => 'balanced', 'Exception' => $e->description, 'type' => 'card error' ]);
				$errors[] = 'Your card was declined. Please try again!';
				$success = false;
			}
			if ($c->id) {
				$success = true;
				$txn = $c->id;
			}
			
		}
		
		if (!$reason && $params['user'] && $params['user']->stripe_id) {
			$reason = true;
			$errors[] = 'Please update your credit card information.';
		}
		
		if (!$reason) {
			$errors[] = 'Not enough card information.';
		}

		return ['status' => $success, 'txn' => $txn, 'errors' => $errors, 'customer' => $this->customer()];

	}
	
	public function customer() {
		return $this->_customer;
	}
}