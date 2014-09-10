<?php

class Controller_api_test extends Crunchbutton_Controller_RestAccount {
	public function init() {

		die('remove this line!');


		$bank_account = 'BA3doORHN1hauK18yRbTGD1h';

		$headers = [ "Accept: application/vnd.api+json;revision=1.1" ];
		$url = 'https://api.balancedpayments.com/bank_accounts/' . $bank_account;
		$auth = c::config()->balanced->{'live'}->secret;
		$request = new \Cana_Curl($url, null, 'get', null, $headers, null, [ 'user' => $auth, 'pass' => '' ] );
		Log::debug( [ 'request' => $request, 'type' => 'claim-account' ] );
		echo '<pre>';var_dump( $request );exit();


	}
}