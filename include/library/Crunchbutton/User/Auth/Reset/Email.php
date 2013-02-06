<?php


class Crunchbutton_User_Auth_Reset_Email extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= $params['email'];
		$params['subject'] 		= 'Customer Password reset';		
		$params['from'] 			= '<tech@_DOMAIN_>';
		$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

		$this->buildView($params);
		$this->view()->subject		= $params['subject'];
		$this->view()->email			= $params['email'];
		$this->view()->message		= $params['message'];
		
		$params['messageHtml']		= $this->view()->render('order/index',['display' => true, 'set' => ['order' => $params['order']]]);

		parent::__construct($params);				
	}
}
