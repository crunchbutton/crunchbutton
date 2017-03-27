<?php

class Crunchbutton_Stripe_Campus_Cash extends Crunchbutton_Charge {

	const ERROR_NOT_FOUND = 'Student ID not found!';

	public function __construct() {}

	public static function retrieve( $stripe_customer, $id_user_payment_type ){

		try {
			$customer = \Stripe\Customer::retrieve( $stripe_customer );
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

		if( !$errors ){
			if( $customer && $customer->metadata && $customer->metadata->campus_cash ){
				Cockpit_Campus_Cash_Log::retrieved( $id_user_payment_type );
				return c::crypt()->decrypt( $customer->metadata->campus_cash );
			} else {
				return self::ERROR_NOT_FOUND;
			}
		} else {
			return join( '.', $errors );
		}
	}

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
				$errors[] = 'Completly vague payment error. Contact support and complain. We love complaints.'."\n\n".'angrycustomers@crunchbutton.com';
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