<?

class Crunchbutton_Message_Push_Ios extends Crunchbutton_Message {
	const SOUND_NEW_ORDER = 'www/new-order.wav';

	public static function send($data) {

		$count = 1;
		$id = 'push';
		$showInForeground = false;

		$message = $data['message'];

		if (isset($data['count'])) {
			$count = $data['count'];
		}

		if (isset($data['sound'])) {
			$sound = $data['sound'];
		} else {
			$sound = 'default';
		}

		if (isset($data['id'])) {
			$id = $data['id'];
		}

		if (isset($data['link'])) {
			$link = $data['link'];
		}

		if (isset($data['showInForeground'])) {
			$showInForeground = $data['showInForeground'];
		}

		// system, blank, or default
		if (isset($data['linkTarget'])) {
			$linkTarget = $data['linkTarget'];
		}

		if (isset($data['category'])) {
			$category = $data['category'];
		}

		$app = $data['app'] == 'cockpit' ? 'cockpit' : 'crunchbutton';

		$env = $data['env'] ? $data['env'] : c::getEnv();

		$to = $data['to'];

		if (!$to || !$message) {
			return false;
		}

		if (!is_array($to)) {
			$to = [$to];
		}

		$message = trim($message);

		$certs = c::config()->dirs->root.'certs/';
		$certname = $app == 'crunchbutton' ? 'com.crunchbutton' : 'com.crunchbutton.cockpit';

		if ($env == 'live') {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
				$certs.'2015.aps_production_'.$certname.'.pem'
			);
		} else {
			$push = new ApnsPHP_Push(
				ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
				$certs.'2015.aps_development_'.$certname.'.pem'
			);
		}

		ob_start();

		$push->setRootCertificationAuthority($certs.'entrust_root_certification_authority.pem');

		try {
			$push->connect();
		} catch (Exception $e) {
			$error = $e->getMessage();
		}

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

			if ($link) {
				// @todo add some sort of cleaning to the url so we cant have multiple //
				$msg->setCustomProperty('link', $link);
				if ($linkTarget) {
					$msg->setCustomProperty('linkTarget', $linkTarget);
				}
			}

			$msg->setCustomProperty('showInForeground', $showInForeground);

			if ($category) {
				$msg->setCategory($category);
			}

			$push->add($msg);
		}

		try {
			$push->send();
			$push->disconnect();
		} catch (Exception $e) {
			$error = $e->getMessage();
		}

		$res = ob_get_contents();
		ob_end_clean();

		$aErrorQueue = $push->getErrors();
		if ($error) {
			$aErrorQueue = array_merge([$error], $aErrorQueue);
		}

		return ['res' => $res, 'status' => $aErrorQueue ? false : true, 'errors' => $aErrorQueue];
	}
}