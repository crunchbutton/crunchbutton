<?php


class Crunchbutton_Email_RulesNotify extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct( $params ) {
		
		$params['to'] 				= $params['email'].', Crunchbutton Copy <_USERNAME_>';
		$params['subject'] 		= $params['subject'];		
		$params['from'] 			= 'Tech Support <tech@_DOMAIN_>';
		$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];
		
		$params['messageHtml'] = $params['message'];

		parent::__construct($params);				
	}
}
