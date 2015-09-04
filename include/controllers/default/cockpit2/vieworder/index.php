<?php

class Controller_vieworder extends Crunchbutton_Controller_Account {
	public function init() {

		$order = Order::uuid( c::getPagePiece( 1 ) );
		if (!$order->id_order ) {
			die('invalid order');
		}

		$cockpit_url = false;

		switch ( c::getPagePiece( 2 ) ) {
			case 'restaurant':
				$version = 'restaurant';
				break;
			case 'customer':
				$version = 'customer';
				break;
			default:
				$version = 'cockpit';
				$cockpit_url = 'https://cockpit.la/' . $order->id_order;
				break;
		}

		$order = $order->get(0);

		$mail = new Email_Order([
			'order' => $order,
			'user' => true,
			'cockpit' => true,
			'cockpit_url' => $cockpit_url,
			'version' => $version
		]);
		echo $mail->message();
		exit;
	}
}