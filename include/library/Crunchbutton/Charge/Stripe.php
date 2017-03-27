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
			$errors[] = 'Missing all card information. Please try entering your card information again.';
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

				} catch(\Stripe\Error\Card $e) {
					$errors[] = $e->getMessage();
				} catch (\Stripe\Error\InvalidRequest $e) {
					$errors[] = 'Invalid parameters for payment request. Try refreshing your page or reloading your app and trying again.';
				} catch (\Stripe\Error\Authentication $e) {
					$errors[] = 'Payment authention failed';
				} catch (\Stripe\Error\ApiConnection $e) {
					$errors[] = 'Connection error communicating with Stripe.';
				} catch (\Stripe\Error\Base $e) {
					$error[] = 'Some wierd error when communicating with Stripe.';

				} catch (Exception $e) {
					$errors[] = 'Could not create a new user for some strange reason.';
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

				} catch(\Stripe\Error\Card $e) {
					$errors[] = $e->getMessage();
				} catch (\Stripe\Error\InvalidRequest $e) {
					$errors[] = 'Invalid parameters for payment request. Try refreshing your page or reloading your app and trying again.';
				} catch (\Stripe\Error\Authentication $e) {
					$errors[] = 'Payment authention failed';
				} catch (\Stripe\Error\ApiConnection $e) {
					$errors[] = 'Connection error communicating with Stripe.';
				} catch (\Stripe\Error\Base $e) {
					$error[] = 'Some wierd error when communicating with Stripe.';

				} catch (Exception $e) {
					$errors[] = 'Could not add new card for some reason. Try using the old one.';
				}
			}

			$this->_card = $params['card']['id'];
		}

		if (!$errors) {
			// Now we have to charge it
			try {
				$charge = \Stripe\Charge::create([
					'amount' => $params['amount'] * 100,
					'currency' => 'usd',
					'customer' => $this->_customer,
					'source' => $this->_card,
					'description' => $params['restaurant']->name,
					'capture' => c::config()->site->config('processor_payments_capture')->value ? true : false,
					'statement_descriptor' => $params['restaurant']->statementName()
				]);

			} catch(\Stripe\Error\Card $e) {
				$errors[] = $e->getMessage();
			} catch (\Stripe\Error\InvalidRequest $e) {
				$errors[] = 'Invalid parameters for payment request. Try refreshing your page or reloading your app and trying again.';
			} catch (\Stripe\Error\Authentication $e) {
				$errors[] = 'Payment authention failed';
			} catch (\Stripe\Error\ApiConnection $e) {
				$errors[] = 'Connection error communicating with Stripe.';
			} catch (\Stripe\Error\Base $e) {
				$error[] = 'Some wierd error when communicating with Stripe.';

			} catch (Exception $e) {
				$errors[] = 'An almost completly vague payment error.';
			}
		}

		if ($charge && $charge->paid && !$charge->refunded) {
			$success = true;
			$txn = $charge->id;
		}

		if (!$success && !$errors) {
			$errors[] = 'Completly vague payment error. Contact support and complain. We love complaints.'."\n\n".'angrycustomers@crunchbutton.com';
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