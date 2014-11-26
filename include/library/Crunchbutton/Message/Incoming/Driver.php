<?php
	
class Crunchbutton_Message_Incoming_Driver extends Cana_Model {

	const ACTION_ACCEPT = 'accept';
	const ACTION_PICKEDUP = 'picked';
	const ACTION_DELIVERED = 'delivered';
	const ACTION_DETAILS = 'details';
	const ACTION_HELP = 'help';

	public function __construct($params) {

		$parsed = $this->parseBody($params['body']);
		$action = $parsed['verb'];

		$order = Order::o(intval($parsed['order']));
		$response = [];

		if ($order->id_order) {

			switch ($action) {

				case self::ACTION_ACCEPT:
					$response = ['msg' => $this->accept($order->setStatus(Crunchbutton_Order_Action::DELIVERY_ACCEPTED, true, $params['admin']), $order), 'stop' => true];
					break;

				case self::ACTION_PICKEDUP:
					$response = ['msg' => $this->pickedup($order->setStatus(Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $params['admin']), $order), 'stop' => true];
					break;

				case self::ACTION_DELIVERED:
					$response = ['msg' => $this->delivered($order->setStatus(Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $params['admin']), $order), 'stop' => true];
					break;

				case self::ACTION_DETAILS:
					$response = ['msg' => $this->details($order), 'stop' => true];
					break;

				case self::ACTION_HELP:
					$response = ['msg' => $this->help($order), 'stop' => false];
					break;

			}

		} elseif ($action == self::ACTION_HELP) {
			$response = ['msg' => $this->help(), 'stop' => false];
		}

		$this->response = (object)$response;
	}

	public function help($order = null) {
		$response = 
			"Driver command usage: ".($order ? $order->id_order : 'order')." command\n".
			"Commands: \n".
			"    accept (or a)\n".
			"    picked (or p)\n".
			"    delivered (or d)\n".
			"    details\n".
			"Ex:   ".($order ? $order->id_order : '123')." p";

		$this->log( [ 'action' => 'help requested', 'invalidOrder' => $invalidOrder ] );
		return $response;
	}

	public function accept($success, $order) {
		if ($success) {
			$response = 'Order #' . $order->id_order . ' accepted';
			$this->log( [ 'action' => 'order accepted', 'id_order' => $order->id_order ] );
		} else {
			$response = 'Error accepting order #' . $order->id_order;
			$this->log( [ 'action' => 'accept error', 'id_order' => $order->id_order ] );
		}
		return $response;
	}

	public function pickedup($success, $order) {
		if ($success) {
			$response = 'Changed order #' . $order->id_order . ' status to picked up';
			$this->log( [ 'action' => 'order picked up', 'id_order' => $order->id_order ] );
		} else {
			$response = 'Error changing order #' . $order->id_order . ' status to picked up';
			$this->log( [ 'action' => 'picked up error', 'id_order' => $order->id_order ] );
		}
		return $response;
	}

	public function delivered($success, $order) {
		if ($success) {
			$response = 'Changed order #' . $order->id_order . ' status to delivered';
			$this->log( [ 'action' => 'order delivered', 'id_order' => $order->id_order ] );
		} else {
			$response = 'Error changing order #' . $order->id_order . ' status to delivered';
			$this->log( [ 'action' => 'delivered error', 'id_order' => $order->id_order ] );
		}
		return $response;
	}

	public function details($order){
		$response = $order->message('sms-driver');
		$this->log( [ 'action' => 'details requested', 'id_order' => $order->id_order ] );
		return $response;
	}

	public function parseBody($body) {
		$body = strtolower($body);

		$verbs = [
			self::ACTION_ACCEPT => [ 'accept', 'acept', 'a' ],
			self::ACTION_PICKEDUP => [ 'picked up', 'picked', 'pick', 'got', 'up', 'p' ],
			self::ACTION_DELIVERED => [ 'delivered', 'd' ],
			self::ACTION_DETAILS => [ 'details', 'deets', 'det' ],
			self::ACTION_HELP => [ 'help', 'h', 'info', 'commands', '\?', 'support']
		];
		

		foreach ($verbs[self::ACTION_HELP] as $k => $verb) {
			$help .= ($help ? '$|^' : '').'\/?'.$verb;
		}

		if (preg_match('/^'.$help.'$/',$body)) {
			return ['verb' => self::ACTION_HELP, 'order' => null];
		}

		foreach ($verbs as $verb =>  $verbList) {
			foreach ($verbList as $v) {
				if (preg_match('/^((#)?([0-9]+) \/?('.$v.'))|(\/?('.$v.') (#)?([0-9]+))$/', $body, $matches)) {
					if ($matches[5]) {
						return ['verb' => $verb, 'order' => $matches[8]];
					} elseif ($matches[1]) {
						return ['verb' => $verb, 'order' => $matches[3]];
					}
					return false;
				}
			}
		}

		return false;
	}

	public function log($content) {
		Log::debug( array_merge ( $content, [ 'type' => 'driver-sms' ] ) );
	}
}
