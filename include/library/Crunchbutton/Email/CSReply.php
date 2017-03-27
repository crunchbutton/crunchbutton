<?php

class Crunchbutton_Email_CSReply extends Email{
	private $_mailConfig;

	public function __construct( $params ) {
		$params['to'] 			= $params[ 'to' ];
		$params['subject'] 	= $params[ 'subject' ];
		$params['from'] 		= 'Crunchbutton <support@crunchbutton.com>';
		$params['reply']		= 'Crunchbutton <support@crunchbutton.com>';

		$this->buildView($params);

		$params['messageHtml'] = $this->view()->render('cs/customerservice',['display' => true, 'set' => ['message' => $params['message'], 'name' => $params['name'] ]]);

		parent::__construct($params);
	}
}