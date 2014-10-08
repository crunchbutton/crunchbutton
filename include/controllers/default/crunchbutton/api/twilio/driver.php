<?php
class Controller_api_twilio_driver extends Crunchbutton_Controller_Rest {

	const ACTION_ACCEPT = 'accept';
	const ACTION_PICKEDUP = 'picked';
	const ACTION_DELIVERED = 'delivered';
	const ACTION_DETAILS = 'details';
	const ACTION_HELP = 'help';
	const ACTION_NONE = 'none';

	public function init() {

		$phone = str_replace( '+1', '', $_REQUEST[ 'From' ] );
		$body = trim( $_REQUEST[ 'Body' ] );

		if( trim( $phone ) == '' ){
			$this->adminError();
			$this->log( [ 'action' => 'admin not found', 'phone' => $phone, 'body' => $body ] );
			exit();
		}

		$admin = Admin::getByPhone( $phone );

		if( !$admin->id_admin ){
			$this->adminError();
			$this->log( [ 'action' => 'admin not found', 'phone' => $phone, 'body' => $body ] );
			exit();
		}

		$this->log( [ 'action' => 'message received', 'id_admin' => $admin->id_admin, 'name' => $admin->name, 'phone' => $phone, 'body' => $body ] );

		$action = $this->parseVerb( $body );
		$id_order = $this->parseOrder( $body );

		$order = Order::o( $id_order );

		if( $order->id_order ){

			switch ( $action ) {

				case Controller_api_twilio_driver::ACTION_ACCEPT:
					$this->accept( $order->deliveryAccept( $admin, true ), $id_order );
					break;

				case Controller_api_twilio_driver::ACTION_PICKEDUP:
					$this->picked( $order->deliveryPickedup( $admin ), $id_order );
					break;

				case Controller_api_twilio_driver::ACTION_DELIVERED:
					$this->delivered( $order->deliveryDelivered( $admin ), $id_order );
					break;

				case Controller_api_twilio_driver::ACTION_DETAILS:
					$this->details( $order );
					break;

				case Controller_api_twilio_driver::ACTION_HELP:
					$this->help();
					break;

				case Controller_api_twilio_driver::ACTION_NONE:
				default:
					$this->help();
					break;
			}

		} else {
			if( $action == Controller_api_twilio_driver::ACTION_HELP ){
				$this->help();
			} else {
				$this->help( true );
			}
		}
	}

	public function adminError(){
		echo $this->response( false );
	}

	public function help( $invalidOrder = false ){
		if( $invalidOrder ){
			echo $this->response( 'Invalid order.' );
		} else {
			echo $this->response( 'You should use "order command".' . "\n" . 'Accepted commands: accept (or a), picked (or p), delivered (or d) or details. ' . "\n" . 'eg. 123 p' );
		}
		$this->log( [ 'action' => 'help requested', 'invalidOrder' => $invalidOrder ] );
	}

	public function accept( $success, $id_order ){
		if( $success ){
			echo $this->response( 'Order #' . $id_order . ' accepted' );
			$this->log( [ 'action' => 'order accepted', 'id_order' => $id_order ] );
		} else {
			echo $this->response( 'Error accepting the order #' . $id_order );
			$this->log( [ 'action' => 'accept error', 'id_order' => $id_order ] );
		}
	}

	public function picked( $success, $id_order ){
		if( $success ){
			echo $this->response( 'Changed order #' . $id_order . ' status to picked up' );
			$this->log( [ 'action' => 'order picked up', 'id_order' => $id_order ] );
		} else {
			echo $this->response( 'Error changing order #' . $id_order . ' status to picked up' );
			$this->log( [ 'action' => 'picked up error', 'id_order' => $id_order ] );
		}
	}

	public function delivered( $success, $id_order ){
		if( $success ){
			echo $this->response( 'Changed order #' . $id_order . ' status to delivered' );
			$this->log( [ 'action' => 'order delivered', 'id_order' => $id_order ] );
		} else {
			echo $this->response( 'Error changing order #' . $id_order . ' status to delivered' );
			$this->log( [ 'action' => 'delivered error', 'id_order' => $id_order ] );
		}
	}

	public function details( $order ){
		echo $this->response( $order->message( 'sms-driver' ) );
		$this->log( [ 'action' => 'details requested', 'id_order' => $order->id_order ] );
	}

	public function response( $content ){
		header( 'Content-type: text/xml' );
		if( $content ){
			$content = '<SMS>' . $content . '</SMS>';
		} else {
			$content = '';
		}
		return '<?xml version="1.0" encoding="UTF-8"?>'."\n" .'<Response>' . $content . '</Response>';
	}

	public function parseOrder( $words ){
		$words = explode( ' ', $words );
		foreach( $words as $word ){
			$word = trim( $word );
			if( is_numeric( $word ) ){
				return intval( $word );
			}
		}
		return false;
	}

	public function parseVerb( $words ){

		$accept = [ 'accept', 'acept', 'a' ];
		$picked = [ 'picked up', 'picked', 'got', 'up', 'p' ];
		$delivered = [ 'delivered', 'd' ];
		$details = [ 'details' ];
		$help = [ 'help', 'h' ];

		$words = explode( ' ', $words );

		foreach( $words as $word ){
			$word = trim( $word );
			if( is_numeric( $word ) ){
				continue;
			}
			$word = strtolower( $word );
			// accept
			if( in_array( $word, $accept ) ){
				return Controller_api_twilio_driver::ACTION_ACCEPT;
			}
			// picked up
			if( in_array( $word, $picked ) ){
				return Controller_api_twilio_driver::ACTION_PICKEDUP;
			}
			// delivered
			if( in_array( $word, $delivered ) ){
				return Controller_api_twilio_driver::ACTION_DELIVERED;
			}
			// details
			if( in_array( $word, $details ) ){
				return Controller_api_twilio_driver::ACTION_DETAILS;
			}
			// help
			if( in_array( $word, $help ) ){
				return Controller_api_twilio_driver::ACTION_HELP;
			}
		}
		return Controller_api_twilio_driver::ACTION_NONE;
	}

	public function log( $content ){
		Log::debug( array_merge ( $content, [ 'type' => 'driver-sms' ] ) );
	}
}
