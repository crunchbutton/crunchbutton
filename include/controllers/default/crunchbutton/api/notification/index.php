<?php

class Controller_api_notification extends Crunchbutton_Controller_Rest {
	public function init() {

		$notification = Notification_Log::o(c::getPagePiece(2));
						
		switch ($this->method()) {
			case 'post':
			case 'get':
				switch (c::getPagePiece(3)) {
					case 'callback':
						switch ($notification->type) {
							case 'phaxio':
								$data = json_decode($_REQUEST['fax']);
								if ($data->id == $notification->remote) {
									$notification->status = 'success';
									$notification->data = $_REQUEST['fax'];
									$notification->save();
									if ($notification->order()->restaurant()->confirmation) {
										$notification->order()->queConfirm();
									}
								}
								break;

							case 'twilio':
								if ($_REQUEST['CallSid'] == $notification->remote) {
									if ($_REQUEST['Duration']) {
										$notification->status = 'success';
									}
									$notification->data = json_encode($_REQUEST);
									$notification->save();
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