<?php

class Crunchbutton_Charge extends Cana_Model {
	public function __construct($params = []) {
	
	}
	
	public static function charge($params = []) {

		Stripe::setApiKey(c::config()->stripe->dev->secret);
		$success = false;
		$reason = false;
		
		// if there is any card information provided, charge with it
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

			// if there was no user, create them
			if (!$user) {
				$user = $params['user'] ? $params['user'] : new User;
				if (!$user->id) {
					$user->name = $params['name'];
					$user->phone = $params['phone'];
					$user->address = $params['address'];
					$user->stripe_id = $customer->id;
					$user->active = 1;
					$user->save();
				}
			} else {
				// if there was already a user, update their info if needed
				$properties = ['name','address','phone','card'];
				foreach ($properties as $property) {
					if ($params[$property] != $user->{$property}) {
						$save = true;
						$user->{$property} = $params[$property];
					}
				}
				if ($save) {
					$user->save();
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