<?php

class Crunchbutton_Email_CSDigest extends Email{
	private $_mailConfig;

	public function __construct( $params ) {
		$params['to'] 			= $params[ 'to' ];
		$params['subject'] 		= 'Customer Service Morning Digest - ' . date( 'm/d/Y' );
		$params['from'] 		= 'Crunchbutton <cc@crunchbutton.com>';
		$params['reply']		= 'Crunchbutton <cc@crunchbutton.com>';

		$this->buildView($params);

		$params['messageHtml'] = $this->view()->render('cs/digest',['display' => true, 'set' => ['tickets' => $params['tickets'], ]]);

		parent::__construct($params);
	}
}