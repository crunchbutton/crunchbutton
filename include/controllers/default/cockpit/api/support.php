<?php

class Controller_api_support extends Crunchbutton_Controller_RestAccount {

	public function init() {

		switch ( c::getPagePiece( 2 ) ) {
			case 'count':
				echo json_encode( [ 'total' => Crunchbutton_Support::pendingSupport()->count() ] );
				break;
			case 'new-chat':
				$params = [];
				$params[ 'Action' ] = 'FakeSMS';
				$params[ 'Name' ] = $this->request()[ 'Name' ];
				$params[ 'Created_By' ] = c::admin()->firstName();
				$params[ 'Body' ] = $this->request()[ 'Body' ];
				$params[ 'From' ] = $this->request()[ 'From' ];
				if( trim( $params[ 'Name' ] ) != '' && trim( $params[ 'Body' ] ) != '' && trim( $params[ 'From' ] ) != '' ){
					$support = Crunchbutton_Support::createNewChat( $params );
					if( $support->id_support ){
							echo json_encode( [ 'success' => $support->id_support ] );
					} else {
						echo json_encode( [ 'error' => 'error creating new chat' ] );
					}
				} else {
					echo json_encode( [ 'error' => 'invalid request' ] );
				}
				break;
			case 'send-sms':
				$message = $this->request()[ 'message' ];
				$phones = explode( ';', $this->request()[ 'phones' ] );
				$phones = array_unique( $phones );

				foreach( $phones as $phone ){
					$admin = Crunchbutton_Admin::getByPhone( $phone );
					if( $admin->id_admin ){
						$name = $admin->firstName();
					} else {
						$name = '';
					}

					$_message = Crunchbutton_Message_Sms::greeting( $name ) . $message;

					Crunchbutton_Message_Sms::send([
						'from' => 'driver',
						'to' => $phone,
						'message' => $_message,
						'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
					] );

					Crunchbutton_Support::createNewWarning(  [ 'dont_open_ticket' => true, 'body' => $_message, 'phone' => $phone ] );

				}
				echo json_encode( [ 'success' => true ] );
				exit();
			case 'add-name':
				$id_support = $this->request()[ 'id_support' ];
				$name = $this->request()[ 'name' ];
				$support = Crunchbutton_Support::o( $id_support );
				if( $support->id_support && trim( $name ) != '' ){
					if( Crunchbutton_Support::addCustomerNameByPhone( $support->phone, $name ) ){
						echo json_encode( [ 'success' => true ] );
					} else {
						echo json_encode( [ 'error' => true ] );
					}
				} else {
					echo json_encode( [ 'error' => 'invalid object' ] );
				}
				break;
		}
	}
}
