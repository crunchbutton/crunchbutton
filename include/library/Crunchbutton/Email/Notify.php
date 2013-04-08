<?php


class Crunchbutton_Email_Notify extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= 'Crunchbutton Copy <_USERNAME_>, nick@_DOMAIN_, david@_DOMAIN_, judd@_DOMAIN_';
		$params['subject'] 		= 'Customer support SMS';
		$params['from'] 			= 'Tech Support <tech@_DOMAIN_>';
		$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

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
