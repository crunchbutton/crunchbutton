<?php

class Controller_api_driver_notifications extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 3 ) ) {
			case 'list':
				$this->_list();
				break;

			case 'change_status':
				$this->_change_status();
				break;

			case 'save':
				$this->_save();
				break;

		}
	}

	private function _notification(){
		$notification = Crunchbutton_Admin_Notification::o( c::getPagePiece( 4 ) );
		if( $notification->id_admin_notification ){
			if( c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) || $notification->id_admin == c::user()->id_admin ){
				return $notification;
			}
		}
		echo json_encode( [ 'error' => 'invalid request' ] );exit;
	}

	private function _save(){
		if( c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){

			switch ( $this->request()[ 'type' ] ) {
				case Crunchbutton_Admin_Notification::TYPE_SMS:
				case Crunchbutton_Admin_Notification::TYPE_DUMB_SMS:
				case Crunchbutton_Admin_Notification::TYPE_PHONE:
					$value = Crunchbutton_Phone::clean( $this->request()[ 'value' ] );
					break;
				case Crunchbutton_Admin_Notification::TYPE_EMAIL:
					$value = filter_var( $this->request()[ 'value' ], FILTER_VALIDATE_EMAIL );
					break;
			}

			if( !$value ){
				echo json_encode( [ 'error' => 'Invalid value!' ] );exit;
			}

			$notification = new Crunchbutton_Admin_Notification;
			$notification->id_admin = $this->request()[ 'id_admin' ];;
			$notification->type = $this->request()[ 'type' ];;
			$notification->value = $value;
			$notification->active = true;
			$notification->save();
			echo json_encode( [ 'success' => true ] );exit;
		} else {
			echo json_encode( [ 'error' => 'invalid request' ] );exit;
		}
	}

	private function _change_status(){

		$_notification = $this->_notification();

		if( $_notification ){
			if( $_notification->active && !c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
				$admin = Admin::o( c::user()->id_admin );
				$notifications = $admin->getNotifications();
				$actives = 0;
				foreach( $notifications as $notification ){
					if( $notification->active ){
						$actives++;
					}
				}

				if( $actives == 1 ){
					echo json_encode( [ 'error' => 'You must to have at least one active notification!' ] );exit;
				}
			}
			$_notification->active = !$_notification->active;
			$_notification->save();
			echo json_encode( [ 'success' => true ] );exit;
		}
	}

	private function _admin(){
		if( c::getPagePiece( 4 ) && c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
			$id_driver = c::getPagePiece( 4 );
			$admin = Admin::login( $id_driver );
			if( !$admin->id_admin ){
				$admin = Admin::o( $id_driver );
			}
		}
		if( !$admin->id_admin ){
			$admin = Admin::o( c::user()->id_admin );
		}
		return $admin;
	}

	private function _list(){
		$driver = $this->_admin();
		$notifications = $driver->getNotifications( ' type DESC ' );
		$out = [ 'driver' => $driver->firstName(), 'id_admin' => $driver->id_admin, 'notifications' => [] ];
		if( c::admin()->permission()->check( [ 'global', 'drivers-all' ] ) ){
			$out[ 'add_notification' ] = true;
		}
		foreach( $notifications as $notification ){
			$out[ 'notifications' ][] = $notification->exports();
		}
		echo json_encode( $out );exit;
	}

}