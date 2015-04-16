<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {

		$env = 'dev';
		\Stripe\Stripe::setApiKey(c::config()->stripe->{$env}->secret);


		// Create a transfer to the specified recipient
		$transfer = \Stripe\Transfer::create(array(
		  "amount" => 1000, // amount in cents
		  "currency" => "usd",
		  "recipient" => 'rp_15sFdPJMXBWnTQ4r8KsQgby1',
		  "bank_account" => 'ba_15sFdCJMXBWnTQ4rDVBYSciO',
		  "statement_descriptor" => "Testing")
		);

		echo '<pre>';var_dump( $transfer );exit();

	}
}