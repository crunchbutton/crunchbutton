<?php


class Crunchbutton_Email_Newusers extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct( $params ) {

		$params['to'] 				= $params['email'];
		$params['subject'] 		= $params['subject'];
		$params['from'] 			= 'Crunchbutton <support@crunchbutton.com>';
		$params['reply']			= 'Crunchbutton <support@crunchbutton.com>';
		$params['reason']			= Crunchbutton_Email_Address::REASON_NEW_USER;

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];
		$this->view()->order		= $params['order'];

		$params['messageHtml']		= $this->view()->render('newusers/index',['display' => true, 'set' => [
			'order' => $params['order'],
			'user' => $params['user']
		]]);

		parent::__construct($params);
	}
}
