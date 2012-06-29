<?php

class Crunchbutton_Charge extends Cana_Model {
	public function __construct($params = []) {
	
	}
	
	public static function charge($params = []) {

		Stripe::setApiKey(c::config()->stripe->dev->secret);
		$success = false;
		
		if ($params['number'] && $params['exp_month'] && $params['exp_year']) {
			try {
				$c = Stripe_Charge::create([
					'amount' => $params['amount'],
					'currency' => 'usd',
					'card' => [
						'number' => $params['number'],
						'exp_month' => $params['exp_month'],
						'exp_year' => $params['exp_year']
					]
				]);
			} catch (Stripe_CardError $e) {
				$success = false;
			}
			if ($c->paid && !$c->refunded) {
				$success = true;
				$txn = $c;
			}
		}
			
		if ($success) {
			$token = Stripe_Token::create([
				'card' => [
					'number' => $params['number'],
					'exp_month' => $params['exp_month'],
					'exp_year' => $params['exp_year']
				]
			]);
			
			if (!$user || !$user->stripe_id) {
				$customer = Stripe_Customer::create([
					'description' => 'Crunchbutton',
					'name' => $params['name'],
					'card' => $token->id
				]);
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
			try {
				$c = Stripe_Charge::create([
					'amount' => 100,
					'currency' => 'usd',
					'customer' => $params['user']->stripe_id
				]);
			} catch (Stripe_CardError $e) {
				$success = false;
			}
			if ($c->paid && !$c->refunded) {
				$success = true;
				$txn = $c;
			}
			
			$user = $params['user'];
			
		}

		return ['status' => $success, 'user' => $user, 'txn' => $txn];

	}
}