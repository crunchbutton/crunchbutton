<?php


class Crunchbutton_User_Auth_Reset_Email extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= $params['email'];
		$params['subject'] 		= 'Crunchbutton password reset';		
		$params['from'] 			= '<support@crunchbutton.com>';
		$params['reply']			= 'Crunchbutton <support@crunchbutton.com>';

		$this->buildView($params);
		$this->view()->subject		= $params['subject'];
		$this->view()->email			= $params['email'];
		$this->view()->message		= $params['message'];
		
		$url = $_SERVER['HTTP_HOST'];

		$params['messageHtml'] = $this->view()->render( 'auth/reset/index', [ 'display' => true, 'set' => [ 'code' => $params[ 'code' ], 'url' => $url ] ] );

		parent::__construct($params);				
	}
}
