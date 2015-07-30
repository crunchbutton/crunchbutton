<?php

class Crunchbutton_Email_Payment_DriverUnverifiedAccount extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct( $data ) {

		// $params['to'] = 'payments@_DOMAIN_';
		$params['to'] = '_EMAIL';
		$params['subject'] = 'Unverified account: ' . $data[ 'driver' ];
		$params['from'] = 'Crunchbutton<support@_DOMAIN_>';
		$params['reply'] = 'Crunchbutton<no-reply@_DOMAIN_>';

		$this->buildView( $params );
		$this->view()->subject = $params['subject'] ;
		$this->view()->email = $params['to'];

		$params['messageHtml'] = $this->view()->render( 'payment/driver-unverified-account',[ 'display' => true, 'set' => [ 'params' => $data ] ] );
		parent::__construct($params);
	}
}
