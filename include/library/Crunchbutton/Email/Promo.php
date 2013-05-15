<?php


class Crunchbutton_Email_Promo extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= $params['email'].', Crunchbutton Copy <_USERNAME_>';
		$params['subject'] 		= $params['subject'];		
		$params['from'] 			= ($params['order']->id_restaurant == 1 ? 'One Button Wenzel' : 'Crunchbutton').' <giftcard@_DOMAIN_>';
		$params['reply']			= 'Crunchbutton<giftcard@_DOMAIN_>';

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];
		
		$params['messageHtml'] = $this->view()->render('promo/index',['display' => true, 'set' => ['content' => $params['message']]]);

		parent::__construct($params);				
	}
}
