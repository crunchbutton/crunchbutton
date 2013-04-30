<?php

class Controller_api_notification extends Crunchbutton_Controller_Rest {
	public function init() {

		$notification = Notification_Log::o(c::getPagePiece(2));
		Log::debug([
			'id_notification_log' => $notification->id_notification_log,
			'id_order' => $notification->id_order,
			'action' => 'notification API : CONFIRMED ' . $notification->order()->confirmed,
			'type' => 'notification'
		]);

		switch ($this->method()) {
			case 'post':
			case 'get':
				switch (c::getPagePiece(3)) {
					case 'order':
						echo json_encode(['order' => $notification->id_order]);
						exit;
						break;
					case '':
						echo $notification->json();
						exit;
						break;

					case 'confirm':
						Log::debug([
							'id_notification_log' => $notification->id_notification_log,
							'id_order' => $notification->id_order,
							'action' => 'notification/confirm',
							'confirmed' => $notification->order()->confirmed,
							'type' => 'notification'
						]);
		
						if ($notification->order()->confirmed) {
							if ($_REQUEST['CallSid'] == $notification->remote) {
								if ($_REQUEST['Duration']) {
									$notification->status = 'success';
								}
								$notification->data = json_encode($_REQUEST);
								$notification->date = date('Y-m-d H:i:s');
								$notification->save();
							} else {
								$notification->status = 'mismatch';
								$notification->data = json_encode($_REQUEST);
								$notification->date = date('Y-m-d H:i:s');
								$notification->save();
							}
						} else {
							$notification->status = 'callback';
							$notification->data = json_encode($_REQUEST);
							$notification->date = date('Y-m-d H:i:s');
							$notification->save();
							$notification->confirm();
						}

						break;

					case 'callback':
						Log::debug([
							'id_notification_log' => $notification->id_notification_log,
							'id_order' => $notification->id_order,
							'action' => 'notification/callback (accepted:' . $notification->order()->accepted() . ')',
							'confirmed' => $notification->order()->confirmed,
							'type' => 'notification'
						]);
						switch ($notification->type) {
							case 'phaxio':
								$data = json_decode($_REQUEST['fax']);

								// Log
								Log::debug( [ 'id_notification_log' => $notification->id_notification_log, 'status' => $data->status, 'action' => 'fax call back', 'data' => $data, 'type' => 'notification' ] );

								// Send a sms to inform about the error.
								if( $data->status != 'success' ){
									$order = $notification->order();
									Crunchbutton_Notification::smsFaxError( $order );
								}

								if ($data->id == $notification->remote) {
									$notification->status = 'success';
									$notification->data = $_REQUEST['fax'];
									$notification->date = date('Y-m-d H:i:s');
									$notification->save();
									if ($notification->order()->restaurant()->confirmation) {
										$notification->order()->queConfirm();
									}
								}
								break;

							case 'twilio':
								if ( $notification->order()->confirmed ) {									
									if ($_REQUEST['CallSid'] == $notification->remote) {
										if ($_REQUEST['Duration']) {
											$notification->status = 'success';
										}
										$notification->data = json_encode($_REQUEST);
										$notification->date = date('Y-m-d H:i:s');
										$notification->save();
									} else {
										$notification->status = 'mismatch';
										$notification->data = json_encode($_REQUEST);
										$notification->date = date('Y-m-d H:i:s');
										$notification->save();
									}
								} else {
									$notification->status = 'callback';
									$notification->data = json_encode($_REQUEST);
									$notification->date = date('Y-m-d H:i:s');
									$notification->save();
									$notification->callback();
								}

								break;
						}
						break;
				}
				
				echo $notification->json();
				exit;
				break;
		}
	}
}