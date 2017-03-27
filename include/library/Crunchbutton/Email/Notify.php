<?php


class Crunchbutton_Email_Notify extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct($params) {

		$params['to'] 				= 'Crunchbutton Copy <cc@crunchbutton.com>, nick@crunchbutton.com, david@crunchbutton.com, judd@crunchbutton.com';
		$params['subject'] 		= 'Customer support SMS';
		$params['from'] 			= 'Crunchbutton <support@crunchbutton.com>';
		$params['reply']			= 'Crunchbutton <support@crunchbutton.com>';
		$params['reason']			= Crunchbutton_Email_Address::REASON_NOTIFY_CS;

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];

		// BDC-TODO
		$params['messageHtml'] = $this->view()->render('promo/index',['display' => true, 'set' => ['content' => $params['message']]]);
		$params['messageHtml'] = $this->view()->render('promo/index',['display' => true, 'set' => ['content' => $params['message']]]);

		parent::__construct($params);
	}
}
