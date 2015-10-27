<?php

class Crunchbutton_Stripe_Campus_Cash extends Crunchbutton_Charge {

	public function __construct() {}

	public function store($params = []) {

		$success = false;

		if (!$params['campus_cash']) {
			$errors[] = 'Missing card information. Please try entering your card information again.';
		}

		if (!$errors) {

			c::stripe();

			$campus_cash = c::crypt()->encrypt( $params[ 'campus_cash' ] );
			$campus_cash_sha1 = sha1( $campus_cash );

			try {
				$customer = \Stripe\Customer::create([
					'description' => $params['name'],
					'email' => $params['email'],
					'metadata' => [ 'campus_cash' => $campus_cash, 'campus_cash_sha1' => $campus_cash_sha1 ]
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

			if ($customer->id) {
				$success = true;
			}

			if (!$success && !$errors) {
				$errors[] = 'Completly vague payment error. Contact support and complain. We love complaints.'."\n\n".'angrycustomers@_DOMAIN_';
			}
		}

		return [
			'status' => $success,
			'txn' => $txn,
			'errors' => $errors,
			'customer' => $this->_customer,
			'campus_cash_sha1' => $campus_cash_sha1
		];

	}
}