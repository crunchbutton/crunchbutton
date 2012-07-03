<?php

class Crunchbutton_Charge extends Cana_Model {
	public function __construct($params = []) {
	
	}
	
	public static function charge($params = []) {

		Stripe::setApiKey(c::config()->stripe->dev->secret);
		$success = false;
		$reason = false;
		
		if ($params['number'] && $params['exp_month'] && $params['exp_year']) {
			$reason = true;
			try {
				$c = Stripe_Charge::create([
					'amount' => $params['amount'] * 100,
					'currency' => 'usd',
					'card' => [
						'number' => $params['number'],
						'exp_month' => $params['exp_month'],
						'exp_year' => $params['exp_year']
					]
				]);
			} catch (Exception $e) {
				print_r($e);
				die('stripe error');
				$success = false;
			}
			if ($c->paid && !$c->refunded) {
				$success = true;
				$txn = $c;
			}
		}

		if ($success) {
		try {
			$token = Stripe_Token::create([
				'card' => [
					'number' => $params['number'],
					'name' => $params['name'],
					'exp_month' => $params['exp_month'],
					'exp_year' => $params['exp_year']
				]
			]);
			} catch (Exception $e) {
			print_r($e);
			die('a');
			}

			if (!$user || !$user->stripe_id) {
			try {
				$customer = Stripe_Customer::create([
					'description' => 'Crunchbutton',
					'card' => $token->id
				]);
			} catch (Exception $e) {
			print_r($e);
			die('b');
			}
			}

			if (!$user) {
				$user = $params['user'] ? $params['user'] : new User;
				if (!$user->id) {
					$user->name = $params['name'];
					$user->phone = $params['phone'];
					$user->stripe_id = $customer->id;
					$user->active = 1;
					$user->save();
				}
			}
		}
		
		if (!$params['number'] && $params['user'] && $params['user']->stripe_id) {
			$reason = true;
			try {
				$c = Stripe_Charge::create([
					'amount' => $params['amount'] * 100,
					'currency' => 'usd',
					'customer' => $params['user']->stripe_id
				]);
			} catch (Exception $e) {
				print_r($e);
				die('stripe error 2');
				$success = false;
			}
			if ($c->paid && !$c->refunded) {
				$success = true;
				$txn = $c;
			}
			
			$user = $params['user'];
			
		}
		
		if (!$reason) {
			$errors[] = 'No card information.';
		}

		return ['status' => $success, 'user' => $user, 'txn' => $txn, 'errors' => $errors];

	}
}