<?php

class Controller_api_temp_pushdevin extends Crunchbutton_Controller_RestAccount {
	public function init() {
		//$c = Crunchbutton_Admin_Notification::q('select * from admin_notification where id_admin=1 and active=true and type=?', [Crunchbutton_Admin_Notification::TYPE_PUSH_IOS])->get(0);

		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => 'addc8d82f9faf739a5c47d10e21041176bd5ba8695bce9e36c6eae47e37c4aac',
			'message' => 'well hello there',
			'count' => rand(1,999),
			'id' => 'new-message',
			'env' => c::getEnv(),
			'app' => 'crunchbutton'
		]);
		
		var_dump($r);
		
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => 'b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800',
			'message' => 'well hello there',
			'count' => rand(1,999),
			'id' => 'new-message',
			'env' => c::getEnv(),
			'app' => 'cockpit'
		]);
		
		var_dump($r);
		
		exit;

		
		$cockpit = ['bda4c763f2e2f2ec8b123a960fd2e9ecba591cf4a310253708156eed658a4bb2','b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800'];
		$cockpit = ['b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800'];
		//$crunchbutton = ['addc8d82f9faf739a5c47d10e21041176bd5ba8695bce9e36c6eae47e37c4aac','a9245362e4a008eba8f701c4d6fb698f7a0c6232c89e689a67ca033f82e40166','b9229ae5121244b9af4309699e878ee615e9f230eb7fd789e22958f89b7ea2ca'];
		$crunchbutton = ['addc8d82f9faf739a5c47d10e21041176bd5ba8695bce9e36c6eae47e37c4aac'];

		$certs = c::config()->dirs->root.'ssl/';
		//$env = 'live';
		$env = 'beta';

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
		
		foreach ($cockpit as $t) {
			$msg = new ApnsPHP_Message($t);
			$msg->setText('please slack me if you recieve this');
			//$msg->setCustomIdentifier();
			//$msg->setSound($sound);
			//$msg->setExpiry(30);
			$msg->setBadge(rand(1,999));
			
			if ($category) {
				//$msg->setCategory('123new');
			}

			$push->add($msg);
		}
		
		$push->send();
		$push->disconnect();

		
		if ($env == 'live') {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
				$certs.'2015.aps_production_com.crunchbutton.pem'
			);
		} else {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
				$certs.'2015.aps_development_com.crunchbutton.pem'
			);
		}
		
		$push->setRootCertificationAuthority($certs.'entrust_root_certification_authority.pem');
		$push->connect();
		
		foreach ($crunchbutton as $t) {
			$msg = new ApnsPHP_Message($t);
			$msg->setCustomIdentifier('order-1');
			$msg->setText('please slack me if you recieve this');
			$msg->setCustomProperty('link', '/work');
			$msg->setCustomProperty('showInForeground', true);
			$msg->setSound('default');
			$msg->setExpiry(30);
			$msg->setBadge(rand(1,999));
			
			$push->add($msg);
		}
		
		$push->send();
		$push->disconnect();

	}
}