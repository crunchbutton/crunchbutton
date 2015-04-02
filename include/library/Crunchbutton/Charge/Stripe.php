<?php

class Crunchbutton_Charge_Stripe extends Crunchbutton_Charge {
	public function __construct($params = []) {
		$this->_customer = $params['customer_id'];
		$this->_card = $params['card_id'];
	}
	
	public function charge($params = []) {
		
		c::stripe();

		$success = false;
		
		if (!$params['card'] && !$this->_customer) {
			$errors[] = 'Missing card or user information from app.';
		}

		// The user changed its card or it is a new one
		if ($params['card']) {
			
			// create a customer if it doesnt exist
			if (!$this->_customer) {
				try {
					$customer = \Stripe\Customer::create([
						'description' => $params['name'],
						'email' => $params['email'],
						'source' => $params['card']['uri']
					]);

				} catch ( Exception $e ) {
					$errors[] = 'Could not create customer with processor.';
				}
				$this->_customer = $customer->id;
						
			// there is already a customer
			} else {
				try {
					$customer = \Stripe\Customer::retrieve($this->_customer);
					$customer->sources->create(['card' => $params['card']['uri']]);
					/* @todo: stripe broke this right now
					$customer->sources->default_source = $params['card']['id'];
					$customer->save();
					*/

				} catch ( Exception $e ) {
					$errors[] = 'Could not retrieve or save customer to processor.';
				}
			}
			
			$this->_card = $params['card']['id'];
		}

		// Now we have to charge it
		try {
			$charge = \Stripe\Charge::create([
				'amount' => $params['amount'] * 100,
				'currency' => 'usd',
				'customer' => $this->_customer,
				'source' => $this->_card,
				'description' => $params['restaurant']->name,
				'capture' => c::config()->site->config('processor_payments_capture') ? true : false,
				'statement_descriptor' => $params['restaurant']->statementName()
			]);

		} catch(\Stripe\Stripe_CardError $e) {
			Log::debug( [ 'card error' => 'card declined', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
			$errors[] = 'Your card was declined. Please try again!';

		} catch (\Stripe\Stripe_InvalidRequestError $e) {
			Log::debug( [ 'card error' => 'invalid request', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
			$errors[] = 'Please update your credit card information.';

		} catch (\Stripe\Stripe_AuthenticationError $e) {
			Log::debug( [ 'card error' => 'auth error', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
			$errors[] = 'Please update your credit card information.';

		} catch (\Stripe\Stripe_ApiConnectionError $e) {
			Log::debug( [ 'card error' => 'api connection', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
			$errors[] = 'Please update your credit card information.';

		} catch (\Stripe\Stripe_Error $e) {
			Log::debug( [ 'card error' => 'api connection', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
			$errors[] = 'Please update your credit card information.';

		} catch (Exception $e) {
			//tripe\Error\InvalidRequest
			//Stripe\Error\Card
			print_r($e);
			$errors[] = 'Error processing credit card.';
		}

		if ($charge && $charge->paid && !$charge->refunded) {
			$success = true;
			$txn = $charge->id;
		} 

		if (!$success && !$errors) {
			$errors[] = 'Completly vague payment error. Contact support and complain. We love complaints.'."\n\n".'angrycustomers@_DOMAIN_';
		}

		return [
			'status' => $success,
			'txn' => $txn,
			'errors' => $errors,
			'customer' => $this->_customer,
			'card' => $this->_card
		];

	}	
}