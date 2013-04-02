<?php


class Crunchbutton_Email_Order extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {
		
		$params['to'] 				= $params['email'].', Crunchbutton Copy <_USERNAME_>';
		$params['subject'] 			= 'Customer Order #'.$params['order']->id_order;		
		$params['from'] 			= ($params['order']->id_restaurant == 1 ? 'One Button Wenzel' : 'Crunchbutton').' <tech@_DOMAIN_>';
		$params['reply']			= 'Tech Support <tech@_DOMAIN_>';

		$this->buildView($params);
		$this->view()->subject		= $params['subject'];
		$this->view()->email		= $params['email'];
		$this->view()->message		= $params['message'];
		
		$params['messageHtml']		= $this->view()->render('order/index',['display' => true, 'set' => [
			'order' => $params['order'],
			'user' => $params['user']
		]]);

		parent::__construct($params);				
	}
}
