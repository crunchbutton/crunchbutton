<?php


class Crunchbutton_Email_Notification extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct($params) {

		$params['to'] 				= $params['email'];
		$params['subject'] 		= $params['title'];
		$params['from'] 			= 'Crunchbutton <hello@_DOMAIN_>';
		$params['reply']			= 'Crunchbutton <hello@_DOMAIN_>';

		$this->buildView($params);
		$this->view()->subject	= $params['title'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];

		$params['messageHtml'] = $this->view()->render('notification/index',['display' => true, 'set' => ['content' => $params['message']]]);

		parent::__construct($params);
	}
}
