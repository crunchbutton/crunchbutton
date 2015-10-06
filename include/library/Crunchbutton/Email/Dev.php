<?php

class Crunchbutton_Email_Dev extends Email{
	private $_mailConfig;

	public function __construct( $params ) {
		$params['to'] 			= $params[ 'to' ];
		$params['subject'] 	= $params[ 'subject' ];
		$params['from'] 		= 'Crunchbutton <_USERNAME_>';
		$params['reply']		= 'Crunchbutton <_USERNAME_>';

		$this->buildView($params);

		$params['messageHtml'] = $params[ 'message' ];

		parent::__construct($params);
	}
}