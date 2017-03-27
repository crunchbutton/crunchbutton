<?php


class Crunchbutton_Email_Promo extends Crunchbutton_Email {
	private $_mailConfig;

	public function __construct($params) {

		$params['to'] 				= $params['email'];
		$params['subject'] 		= $params['subject'];
		$params['from'] 			= ($params['order']->id_restaurant == 1 ? 'One Button Wenzel' : 'Crunchbutton - Gift card').' <giftcard@crunchbutton.com>';
		$params['reply']			= 'Crunchbutton - Gift card<giftcard@crunchbutton.com>';
		$params['reason']			= Crunchbutton_Email_Address::REASON_PROMO;

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];

		$params['messageHtml'] = $this->view()->render('promo/index',['display' => true, 'set' => ['content' => $params['message']]]);

		parent::__construct($params);
	}
}
