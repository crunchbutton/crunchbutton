<?php

class Crunchbutton_Email_CSDigest extends Email{
	private $_mailConfig;

	public function __construct( $params ) {
		$params['to'] 			= $params[ 'to' ];
		$params['subject'] 		= 'Customer Service Morning Digest - ' . date( 'm/d/Y' );
		$params['from'] 		= 'Crunchbutton <_USERNAME_>';
		$params['reply']		= 'Crunchbutton <_USERNAME_>';

		$this->buildView($params);

		$params['messageHtml'] = $this->view()->render('cs/digest',['display' => true, 'set' => ['messages' => $params['messages'], ]]);

		parent::__construct($params);
	}
}