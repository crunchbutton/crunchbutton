<?php

class Controller_api_order extends Crunchbutton_Controller_RestAccount {
	public function init() {

		$order = Order::uuid(c::getPagePiece(2));
		if (!$order->id_order) {
			$order = Order::o(c::getPagePiece(2));
		}

		if (!$order->id_order) {
			echo json_encode(['error' => 'invalid object']);
			exit;

		} else {
			if (get_class($order) != 'Cockpit_Order') {
				$order = $order->get(0);
			}
		}

		if (1==1 || $this->method() == 'post') {
			$res = [];

			switch (c::getPagePiece(3)) {
				case 'delivery-pickedup':
					$res['status'] = $order->deliveryPickedup(c::admin());
					break;

				case 'delivery-delivered':
					$res['status'] = $order->deliveryDelivered(c::admin());
					break;

				case 'delivery-accept':
					$res['status'] = $order->deliveryAccept(c::admin(), true);
					break;

				case 'delivery-reject':
					$order->deliveryReject(c::admin());
					$res['status'] = true;
					break;
			}
		}

		if ($order->deliveryStatus())
		$ret = $order->deliveryExports();
		$ret['status'] = $res['status'];

		echo json_encode($ret);
		exit;

	}
}