<?php

class Crunchbutton_Charge_Stripe extends Crunchbutton_Charge {
	public function __construct($params = []) {
	
	}
	
	public function charge($params = []) {
		$env = c::env() == 'live' ? 'live' : 'dev';
		Stripe::setApiKey(c::config()->stripe->{$env}->secret);
		$success = false;
		$reason = false;
		
		// if there is any card information provided, charge with it
		if ($params['number']) {
			$reason = true;
			try {
				$c = Stripe_Charge::create([
					'amount' => $params['amount'] * 100,
					'currency' => 'usd',
					'description' => $params['restaurant']->name,
					'card' => [
						'number' => $params['number'],
						'exp_month' => $params['exp_month'],
						'exp_year' => $params['exp_year'],
						'name' => $params['name'],
					]
				]);
			} catch (Exception $e) {
				$errors[] = 'Your card was declined. Please try again!';
				$success = false;
			}
			if ($c->paid && !$c->refunded) {
				$success = true;
				$txn = $c->id;
			}
		}

		if ($success) {
			// if the transaction was a success, create the token
			$params['card'] = substr($params['number'], -4);

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
				// if there is no user, create one
				try {
					$customer = Stripe_Customer::create([
						'description' => 'Crunchbutton',
						'card' => $token->id
					]);
				} catch (Exception $e) {
					print_r($e);
					die('b');
				}

			} elseif ($user && $user->strip_id) {
				// if there is already a user, update it
				try {
					$customer = Stripe_Customer::retrieve($user->strip_id);
					$customer->card = $token->id;
					$customer->save();
				} catch (Exception $e) {
					print_r($e);
					die('c');
				}
			}
		}
		
		// if there was no number, and there was a user with a stored card, use the users stored card
		if (!$params['number'] && $params['user'] && $params['user']->stripe_id) {
			$reason = true;
			try {
				$c = Stripe_Charge::create([
					'amount' => $params['amount'] * 100,
					'currency' => 'usd',
					'customer' => $params['user']->stripe_id
				]);
			} catch (Exception $e) {
				$errors[] = 'Your card was declined. Please try again!';
				$success = false;
			}
			if ($c->paid && !$c->refunded) {
				$success = true;
				$txn = $c->id;
			}
			
		}
		
		if (!$reason) {
			$errors[] = 'Not enough card information.';
		}

		return ['status' => $success, 'txn' => $txn, 'errors' => $errors, 'customer' => $customer];

	}
}