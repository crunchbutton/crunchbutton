<?php


class Crunchbutton_Email_Order extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['toEmail'] 				= '_EMAIL';
		$params['toName'] 				= 'TREST';	
		$params['subject'] 				= 'Contact';		
		$params['fromEmail'] 			= 'ubertest@arzynik.com';
		$params['replyTo']				= 'replytome@arzynik.com';
		$params['fromName'] 			= 'TRESTER';

		$this->buildView($params);
		$this->view()->subject		= $params['subject'];
		$this->view()->email	= $params['email'];
		$this->view()->message	= $params['message'];
		
		$params['messageHtml']			= $this->view()->render('order/index',['display' => true, 'set' => ['order' => $params['order']]]);
		parent::__construct($params);				
	}
}
