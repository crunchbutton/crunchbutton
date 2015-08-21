<?php

class Controller_api_temp_pushdevin extends Crunchbutton_Controller_RestAccount {
	public function init() {
		$c = Crunchbutton_Admin_Notification::q('select * from admin_notification where id_admin=1 and active=true and type=?', [Crunchbutton_Admin_Notification::TYPE_PUSH_IOS])->get(0);
/*
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => $c->value,
			'message' => 'test',
			'count' => 1,
			'id' => 'order-'.rand(10000, 50000000),
			'category' => 'order-new-test',
			'env' => c::getEnv()
		]);
		*/
		
		

		$certs = c::config()->dirs->root.'ssl/';
$env = 'live';
		// @todo: change this after aproved
		if ($env == 'live') {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
				$certs.'2015.aps_production_com.crunchbutton.cockpit.pem'
			);
		} else {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
				$certs.'2015.aps_development_com.crunchbutton.cockpit.pem'
			);
		}

		$push->setRootCertificationAuthority($certs.'entrust_root_certification_authority.pem');
		
		
			$push->connect();
//b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800

			$msg = new ApnsPHP_Message('8d9b2a99aa4754686eb76ff3a20c007c808470a7327107e786f6cf0e1696f7ac');
			$msg->setCustomIdentifier('i22');
			$msg->setText('please slack me if you recieve this');
			//$msg->setSound($sound);
			$msg->setExpiry(30);
	
			$msg->setBadge(1);
			
			if ($category) {
				//$msg->setCategory('123new');
			}

	
			$push->add($msg);

		
			$push->send();
			$push->disconnect();

		var_dump($aErrorQueue);
	}
}