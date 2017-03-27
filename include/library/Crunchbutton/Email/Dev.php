<?php

class Crunchbutton_Email_Dev extends Email{
	private $_mailConfig;

	public function __construct( $params ) {
		$params['to'] 			= $params[ 'to' ];
		$params['subject'] 	= $params[ 'subject' ];
		$params['from'] 		= 'Crunchbutton <cc@crunchbutton.com>';
		$params['reply']		= 'Crunchbutton <cc@crunchbutton.com>';

		$this->buildView($params);

		$params['messageHtml'] = $params[ 'message' ];

		parent::__construct($params);
	}
}