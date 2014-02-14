<?php

class Controller_vieworder extends Cana_Controller {
	public function init() {

		$order = Order::uuid( c::getPagePiece( 1 ) );
		if (!$order->id_order || (!$_SESSION['admin'] && c::user()->id_user != $order->id_user)) {
			die('invalid order');
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'restaurant':
				$version = 'restaurant';
				break;
			case 'customer':
				$version = 'customer';
				break;
			default:
				$version = 'cockpit';
				break;
		}
		

		$order = $order->get(0);

		$mail = new Email_Order([
			'order' => $order,
			'user' => true,
			'cockpit' => true,
			'version' => $version
		]);
		echo $mail->message();
		exit;
	}
}