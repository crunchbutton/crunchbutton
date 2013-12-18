<?php

class Crunchbutton_Charge_Stripe extends Crunchbutton_Charge {
	public function __construct($params = []) {
	
	}
	
	public function charge($params = []) {
		
		$env = c::getEnv();
		
		Stripe::setApiKey(c::config()->stripe->{$env}->secret);

		$success = false;
		$reason = false;

		$user = $params[ 'user' ];

		// Start with no customer id
		$customer_id = false;

		// The user changed its card or it is a new one
		if( $params['card']['id'] ){
			// The first thing we need to do is check customer
			$token = $params['card']['uri'];
			// lets see if the customer exists
			if ( !$user || !$user->payment_type()->stripe_id ) {
					// if there is no user, create one
					try {
						$customer = Stripe_Customer::create( array(
													'description' => "Crunchbutton",
													'card' => $token
												) );
					} catch ( Exception $e ) {
						print_r( $e );
						die('creating customer error: 1');
					}
				} elseif ( $user && $user->payment_type()->stripe_id ) {
					// if there is already a user, update it
					try {
						$customer = Stripe_Customer::retrieve( $user->payment_type()->stripe_id );
						$customer->card = $token;
						$customer->save();
					} catch ( Exception $e ) {
						print_r( $e );
						die('creating customer error: 2');
					}
				}
				$customer_id = $customer->id;
			} 
			// If we don't have a card token it means the user is already a customer
			else if( $user->payment_type()->stripe_id ) {
				$customer_id = $user->payment_type()->stripe_id;
			}

			// yay, we have a valid customer
			if( $customer_id ){
				// Now we have to charge it
				try {
					$charge = Stripe_Charge::create([
						'amount' => $params['amount'] * 100,
						'currency' => 'usd',
						'customer' => $customer_id,
						'description' => $params['restaurant']->name,
					] );
				} 
				// Shit happens
				catch(Stripe_CardError $e) {
					Log::debug( [ 'card error' => 'card declined', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
					$errors[] = 'Your card was declined. Please try again!';
				} catch (Stripe_InvalidRequestError $e) {
					Log::debug( [ 'card error' => 'invalid request', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
					$errors[] = 'Please update your credit card information.';
				} catch (Stripe_AuthenticationError $e) {
					Log::debug( [ 'card error' => 'auth error', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
					$errors[] = 'Please update your credit card information.';
				} catch (Stripe_ApiConnectionError $e) {
					Log::debug( [ 'card error' => 'api connection', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
					$errors[] = 'Please update your credit card information.';
				} catch (Stripe_Error $e) {
					Log::debug( [ 'card error' => 'api connection', 'Exception' => $e->getJsonBody(), 'type' => 'stripe error' ]);
					$errors[] = 'Please update your credit card information.';
				} catch (Exception $e) {
					$errors[] = 'Not enough card information.';
				}

			if ( $charge->paid && !$charge->refunded ) {
				$success = true;
				$txn = $charge->id;
			} 
		} 
		if (!$success && !$errors) {
			$errors[] = 'Not enough card information.';
		}

		return [ 'status' => $success, 'txn' => $txn, 'errors' => $errors, 'customer' => $customer ];

	}	
}