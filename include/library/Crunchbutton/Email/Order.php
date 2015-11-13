<?php

class Crunchbutton_Email_Order extends Crunchbutton_Email {

	private $_mailConfig;

	public function __construct($params) {

		$params['to'] = $params['email'];
		$params['subject'] = 'Customer Order #'.$params['order']->id_order;
		$params['from'] = ($params['order']->id_restaurant == 1 ? 'One Button Wenzel' : 'Crunchbutton').' <support@_DOMAIN_>';
		$params['reply'] = 'Crunchbutton <support@_DOMAIN_>';
		$params['reason'] = Crunchbutton_Email_Address::REASON_ORDER;

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

		$params['messageHtml'] = $this->view()->render('order/index',['display' => true, 'set' => [
			'order' => $params['order'],
			'signature' => $params['signature'],
			'user' => $params['user'],
			'cockpit' => $params['cockpit'],
			'show_credit_card_tips' => $params['show_credit_card_tips'],
			'show_delivery_fees' => $params['show_delivery_fees'],
			'version' => $version,

		]]);

		parent::__construct($params);
	}
}
