<?php

class Controller_vieworder extends Cana_Controller {
	public function init() {

		$order = Order::uuid(c::getPagePiece(1));
		if (!$order->id_order) {
			die('invalid order');
		}
		$order = $order->get(0);

		$mail = new Email_Order([
			'order' => $order
		]);
		echo $mail->message();
		exit;
	}
}