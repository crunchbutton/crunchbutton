<?php

class Crunchbutton_Message_Incoming_Driver extends Cana_Model {

	const ACTION_ACCEPT = 'accept';
	const ACTION_PICKEDUP = 'picked';
	const ACTION_DELIVERED = 'delivered';
	const ACTION_DETAILS = 'details';
	const ACTION_HELP = 'help';
	const ACTION_SHIFT_CONFIRMATION = 'shift-confirmation';
	const ACTION_SHIFT_CONFIRMATION_HELP = 'shift-confirmation-help';
	const ACTION_DRIVER_HELP_OUT_STOP_NOTIFICATION = 'stop-help-out-notification';

	public function __construct($params) {

		$params['body'] = trim( $params['body'] );

		$parsed = $this->parseBody($params['body'], $params[ 'from' ]);

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

		} elseif ($action == self::ACTION_SHIFT_CONFIRMATION ){
			if( $parsed[ 'id_admin_shift_assign' ] ){
				$message = Crunchbutton_Admin_Shift_Assign_Confirmation::confirmShiftBySMS( $parsed[ 'id_admin_shift_assign' ] );
				$response = ['msg' => $message, 'stop' => true ];
			}

		} elseif ( $action == self::ACTION_SHIFT_CONFIRMATION_HELP ){
			// do nothing
		} elseif ( $action == self::ACTION_DRIVER_HELP_OUT_STOP_NOTIFICATION ){
			$response = ['msg' => $message, 'stop' => true ];
		}

		$this->response = (object) $response;
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

	public function parseBody($body, $phone = false ) {

		$body = strtolower( $body );

		$verbs = [
			self::ACTION_ACCEPT => [ 'accept', 'acept', 'a' ],
			self::ACTION_PICKEDUP => [ 'picked up', 'picked', 'pick', 'got', 'up', 'p' ],
			self::ACTION_DELIVERED => [ 'delivered', 'd' ],
			self::ACTION_DETAILS => [ 'details' ],
			self::ACTION_SHIFT_CONFIRMATION => [ '"yes"!','"yes!','yes"!','yes','yes!','"yes!"', 'yes!!', 'yes!!!' ],
			self::ACTION_DRIVER_HELP_OUT_STOP_NOTIFICATION => [ '"no"!', '"no"','"no!','no"!','no','no!','"no!"' ],
			self::ACTION_HELP => [ 'help', 'h', 'info', 'commands', '\?', 'support']
		];

		// check if the admin has a shift to confirm - #5321
		if( $phone ){
			$shift = Crunchbutton_Admin_Shift_Assign_Confirmation::checkIfPhoneHasShiftToConfirm( $phone );
			if( $shift ){
				foreach ($verbs[self::ACTION_SHIFT_CONFIRMATION] as $k => $verb) {
					if ( strtolower( $body ) == $verb ) {
						return ['verb' => self::ACTION_SHIFT_CONFIRMATION, 'id_admin_shift_assign' => $shift->id_admin_shift_assign ];
					}
				}
				return ['verb' => self::ACTION_SHIFT_CONFIRMATION_HELP, 'id_admin_shift_assign' => $shift->id_admin_shift_assign ];
			}
		}

		// check if the driver has a driver help out to cancel - #7281
		if( $phone ){
			foreach ($verbs[self::ACTION_DRIVER_HELP_OUT_STOP_NOTIFICATION] as $k => $verb) {
				if ( strtolower( $body ) == $verb ) {
					$drivers = Admin::q( 'SELECT * FROM admin WHERE phone = ?', [ $phone ] );
					foreach( $drivers as $driver ){
						if( $driver->couldReceiveHelpOutNotification() ){
							$driver->stopHelpOutNotification();
						}
					}
					return ['verb' => self::ACTION_DRIVER_HELP_OUT_STOP_NOTIFICATION ];
				}
			}
		}

		foreach ($verbs[self::ACTION_HELP] as $k => $verb) {
			$help .= ($help ? '$|^' : '').'\/?'.$verb;
		}

		if (preg_match('/^'.$help.'$/',$body)) {
			return ['verb' => self::ACTION_HELP, 'order' => null];
		}

		foreach ($verbs as $verb =>  $verbList) {
			foreach ($verbList as $v) {
				if (preg_match('/^((#)?([0-9]+) \/?('.$v.'))$|^(\/?('.$v.') (#)?([0-9]+))$/i', $body, $matches)) {
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
