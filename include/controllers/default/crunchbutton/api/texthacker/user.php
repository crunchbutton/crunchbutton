<?php

class Controller_api_texthacker_user extends Crunchbutton_Controller_Rest {
	public function init() {
		$user = User::byPhone($this->request()['phone']);
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