<?php

class Crunchbutton_Email_Order_Stealthfax extends Crunchbutton_Email {
	private $_mailConfig;
	
	public function __construct($params) {

		$params['to'] = $params['email'];
		$params['subject'] = 'Customer Order #'.$params['order']->id_order;		
		$params['from'] = ($params['order']->id_restaurant == 1 ? 'One Button Wenzel' : 'Crunchbutton').' <support@crunchbutton.com>';
		$params['reply'] = 'Crunchbutton <support@crunchbutton.com>';
		$params['reply'] = 'Crunchbutton <support@crunchbutton.com>';

		$this->buildView($params);
		$this->view()->subject = $params['subject'];
		$this->view()->email = $params['email'];
		
		if( $params['cockpit_url'] ){
			$this->view()->cockpit_url	= $params['cockpit_url'];	
		}
		
		$this->view()->message = $params['message'];
		
		if( $params['version'] ){
			$version = $params['version'];
		} else {
			$version = false;
		}

		$params['messageHtml'] = $this->view()->render('order/stealthfax/index',['display' => true, 'set' => [
			'order' => $params['order'],
			'user' => $params['user'],
			'cockpit' => $params['cockpit'],
			'version' => $version
		]]);

		parent::__construct($params);				
	}
}
