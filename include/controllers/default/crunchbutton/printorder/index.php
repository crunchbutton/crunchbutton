<?php

class Controller_printorder extends Cana_Controller {
	public function init() {

		$order = Order::uuid(c::getPagePiece(1));
		if (!$order->id_order) {
			header('HTTP/1.0 404 Not Found');
			exit;
		}
		$order = $order->get(0);
		$order->_print = true;


		$signature = ( $_GET[ 'signature' ] && c::user()->id_admin ) ? true : false;

		if( $signature ){
			$order->_print = false;
		}

		$mail = new Email_Order([
			'order' => $order,
			'signature' => $signature,
			'user' => true
		]);
		echo $mail->message();
		exit;
	}
}
