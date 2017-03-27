<?php


class Crunchbutton_Email_RulesNotify extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] 				= $params['email'];
		$params['subject'] 		= $params['subject'];
		$params['from'] 			= 'Crunchbutton <support@crunchbutton.com>';
		$params['reply']			= 'Crunchbutton <support@crunchbutton.com>';
		$params['reason']			= Crunchbutton_Email_Address::REASON_RULES;

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];

		$params['messageHtml'] = $params['message'];

		parent::__construct($params);
	}
}
