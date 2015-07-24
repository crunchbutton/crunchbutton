<?php

class Controller_Api_Stripe_Webhooks extends Crunchbutton_Controller_Rest {

	public function init() {
		\Stripe\Stripe::setApiKey(c::config()->stripe->{c::getEnv()}->secret);
		$input = @file_get_contents( "php://input" );
		$event = json_decode($input);
		Log::debug(['data'=>$event]);
		Crunchbutton_Stripe_Webhook::create( $event );
		http_response_code(200);
	}
}
