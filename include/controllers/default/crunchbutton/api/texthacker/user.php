<?php

class Controller_api_texthacker_user extends Crunchbutton_Controller_Rest {
	public function init() {
		$phone = $this->request()['phone'];
		$phone = preg_replace('/[^0-9]/','',$phone);

		if (strlen($phone) == 11) {
			$phone = substr($phone, 1, 10);
		}
		$user = User::byPhone($phone);
		if (!$user->count()) {
			echo json_encode([]);
			exit;
		}
		$userExport = $user->get(0)->exports();
		unset($userExport['presets']);

		$order = $user->lastOrder()->get(0)->get(0);
		$orderExport = $order->exports();
		$orderExport['_restaurant'] = $order->restaurant()->properties();

		echo json_encode([
			'user' => $userExport,
			'order' => $orderExport
		]);
	}
}