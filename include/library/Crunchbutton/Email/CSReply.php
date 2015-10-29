<?php

class Crunchbutton_Email_CSReply extends Email{
	private $_mailConfig;

	public function __construct( $params ) {
		$params['to'] 			= $params[ 'to' ];
		$params['subject'] 	= $params[ 'subject' ];
		$params['from'] 		= 'Crunchbutton <support@_DOMAIN_>';
		$params['reply']		= 'Crunchbutton <support@_DOMAIN_>';

		$this->buildView($params);

		$params['messageHtml'] = $this->view()->render('cs/customerservice',['display' => true, 'set' => ['message' => $params['message'], ]]);

		parent::__construct($params);
	}
}