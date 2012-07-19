<?php


class Crunchbutton_Email_Order extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= $params['email'];
		$params['subject'] 				= 'Customer Order';		
		$params['from'] 			= 'Crunchbutton <devin@_DOMAIN_>';
		$params['reply']				= 'replytome@arzynik.com';

		$this->buildView($params);
		$this->view()->subject		= $params['subject'];
		$this->view()->email	= $params['email'];
		$this->view()->message	= $params['message'];
		
		$params['messageHtml']			= $this->view()->render('order/index',['display' => true, 'set' => ['order' => $params['order']]]);

		parent::__construct($params);				
	}
}
