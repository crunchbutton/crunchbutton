<?php
class Controller_api_test_chat extends Crunchbutton_Controller_Account {
	public function init() {
		$message = Support_Message::o(2913);
		$res = Event::emit([
			'room' => [
				'ticket.'.$message->id_support,
				'tickets',
			]
		], 'message', $message->exports());

		
		exit;
		
		$call = new Call([
			'date_start' => date('Y-m-d- H:i:s'),
			'status' => 'in-progress',
			'from' => '_PHONE_',
			'direction' => 'inbound'
		]);
		$call->save();

		
		exit;

		$version = Deploy_Version::o(15);
		$version->version = 'pooasdasdsdsdsd4234234';
		$version->save();

		echo $res;
		
		exit;

		$message = Support_Message::o(2913);
		$res = Event::emit([
			'room' => [
				'ticket.'.$message->id_support,
				'ticket.all',
			]
		], 'ticket.message', $message->exports());

		echo $res;
	}
}