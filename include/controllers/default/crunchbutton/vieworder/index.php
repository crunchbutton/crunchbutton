<?php

class Controller_vieworder extends Cana_Controller {
	public function init() {

		$order = Order::uuid(c::getPagePiece(1));
		if (!$order->id_order || (!$_SESSION['admin'] && c::user()->id_user != $order->id_user)) {
			die('invalid order');
		}
		$order = $order->get(0);

		$mail = new Email_Order([
			'order' => $order,
			'user' => true
		]);
		echo $mail->message();
		exit;
	}
}