<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {
	public function init() {

			$campus_money = new Crunchbutton_Stripe_Customer;
			$money = 'T12345678';
			$success = $campus_money->storeCampusMoney( [ 'campus_cash' => $money, 'name' => 'daniel camargo', 'email' => '_EMAIL' ] );

			echo json_encode( $success );exit;

			$money = c::crypt()->encrypt( 'T12356778' );
			$money_sha1 = sha1($money);

			// $money = c::crypt()->decrypt( 'Fi2awEaIJNMFPIy+6lyLEg==' );

			// echo '<pre>';var_dump( $money );exit();

			c::stripe();

			try {
					$customer = \Stripe\Customer::create([
						'description' => 'Daniel Camargo',
						'email' => 'daniel@_DOMAIN_',
						'metadata' => [ 'campus_cash' => $money, 'campus_cash_sha1' => $money_sha1 ]
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

				if( count( $errors ) ){
					echo '<pre>';var_dump( $errors );exit();
				} else {
					echo '<pre>';var_dump( $customer );exit();
				}

				$this->_customer = $customer->id;

	}
}