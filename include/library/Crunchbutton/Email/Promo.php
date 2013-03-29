<?php


class Crunchbutton_Promo_Order extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= $params['email'].', Crunchbutton Copy <_USERNAME_>';
		$params['subject'] 		= $params['subject'];		
		$params['from'] 			= ($params['order']->id_restaurant == 1 ? 'One Button Wenzel' : 'Crunchbutton').' <tech@_DOMAIN_>';
		$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

		$this->buildView($params);
		$this->view()->subject	= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message	= $params['message'];
		
		$params['messageHtml'] = $this->view()->render('promo/index',['display' => true, 'set' => ['content' => $params['message']]]);

		parent::__construct($params);				
	}
}
