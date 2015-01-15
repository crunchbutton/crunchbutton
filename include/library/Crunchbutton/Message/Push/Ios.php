<?

class Crunchbutton_Message_Push_Ios extends Crunchbutton_Message {
	public static function send($to, $message = null, $id = null, $count = null) {

		$sound = 'www/edm.wav';
		$count = 1;
		$id = 'push';
		$category = '';

		if (is_array($to)) {

			$message = $to['message'];

			if (isset($to['count'])) {
				$count = $to['count'];
			}
			
			if (isset($to['sound'])) {
				$sound = $to['sound'];
			}
			
			if (isset($to['id'])) {
				$id = $to['id'];
			}
			
			if (isset($to['category'])) {
				$category = $to['category'];
			}
			
			$to = $to['to'];
		}

		if (!$to || !$message) {
			return false;
		}
		
		if (!is_array($to)) {
			$to = [$to];
		}
		
		$message = trim($message);

		$env = c::getEnv();

		$certs = c::config()->dirs->root.'ssl/';

		// @todo: change this after aproved
		if (1==2 && $env == 'live') {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
				$certs.'aps_production_com.crunchbutton.cockpit.pem'
			);
		} else {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
				$certs.'aps_development_com.crunchbutton.cockpit.pem'
			);
		}

		$push->setRootCertificationAuthority($certs.'entrust_root_certification_authority.pem');
		$push->connect();

		foreach ($to as $t) {
		
			if (!$t) {
				continue;
			}

			$msg = new ApnsPHP_Message($t);
			$msg->setCustomIdentifier($id);
			$msg->setText($message);
			$msg->setSound($sound);
			$msg->setExpiry(30);
	
			$msg->setBadge($count);
			
			if ($category) {
				$msg->setCategory($category);
			}

	
			$push->add($msg);
		}

		$push->send();
		$push->disconnect();

		$aErrorQueue = $push->getErrors();

		return $aErrorQueue ? $aErrorQueue : true;
	}
}