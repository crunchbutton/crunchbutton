<?php


class Crunchbutton_Email_Feedback extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct($params) {

		$params['to'] 				= 'drivers@crunchbutton.com';
		$params['subject'] 		= 'Driver Feedback';
		$params['from'] 			= 'Crunchbutton <cc@crunchbutton.com>';
		$params['reply']			= 'Crunchbutton <cc@crunchbutton.com>';
		//$params['reason']			= Crunchbutton_Email_Address::REASON_NOTIFY_CS;

		$this->buildView($params);
		$this->view()->community	= $params['community'];
		$this->view()->name		= $params['name'];
		$this->view()->message	= $params['message'];

		// BDC-TODO
		$params['messageHtml'] = $this->view()->render('driver/feedback',['display' => true, 'set' => 
			['community' => $params['community'], 
			'name' => $params['name'],
			'message' => $params['message']]]);
		
		parent::__construct($params);
	}
}
