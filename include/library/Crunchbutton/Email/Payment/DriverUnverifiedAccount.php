<?php

class Crunchbutton_Email_Payment_DriverUnverifiedAccount extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $data ) {

		$params['to'] = 'payments@crunchbutton.com';
		$params['subject'] = 'Unverified account: ' . $data[ 'driver' ];
		$params['from'] = 'Crunchbutton<support@crunchbutton.com>';
		$params['reply'] = 'Crunchbutton<no-reply@crunchbutton.com>';

		$this->buildView( $params );
		$this->view()->subject = $params['subject'] ;
		$this->view()->email = $params['to'];

		$params['messageHtml'] = $this->view()->render( 'payment/driver-unverified-account',[ 'display' => true, 'set' => [ 'params' => $data ] ] );
		parent::__construct($params);
	}
}
