<?php

class Crunchbutton_Message_Incoming_Support extends Cana_model {

	const ACTION_CLOSE = 'close';
	const ACTION_OPEN = 'open';
	const ACTION_DETAILS = 'details';
	const ACTION_REPLY = 'reply';
	const ACTION_HELP = 'help';

	public function __construct($params) {

		$parsed = $this->parseBody($params['body']);
		$action = $parsed['verb'];
		$this->message = $parsed['message'];
		$this->support = Support::o(intval($parsed['support']));
		$this->admin = $params['admin'];
		$this->body = $params['body'];
		$this->from = $params['from'];
		$this->media = $params['media'];
		$response = [];

		if ($this->support->id_support) {

			switch ($action) {

				case self::ACTION_CLOSE:
					$response = ['msg' => $this->close(), 'stop' => true];
					break;

				case self::ACTION_REPLY:
					$response = ['msg' => $this->reply(), 'stop' => true];
					break;

				case self::ACTION_DETAILS:
					$response = ['msg' => $this->status(), 'stop' => true];
					break;

				case self::ACTION_HELP:
					$response = ['msg' => $this->help($order), 'stop' => false];
					break;
			}

		} elseif ($action == self::ACTION_HELP) {
			$response = ['msg' => $this->help(), 'stop' => false];
		}

		$this->response = (object)$response;
	}

	public function close() {
		$this->support->addSystemMessage($this->admin->name . ' closed the message from text message.' );
		$this->support->status = Crunchbutton_Support::STATUS_CLOSED;
		$this->support->save();

		$this->log( [ 'action' => 'closing support', 'id_support' => $this->support->id_support, 'phone' => $this->from, 'message' => $this->body] );

		self::notifyReps($this->admin->firstName() . ' closed #' . $this->support->id_support, $support, null, $this->admin);
	}

	public function reply() {

		$this->support->addAdminMessage( [
			'phone' => $this->from,
			'body' => $this->message,
			'media' => $this->media
		] );

		$this->log( [ 'action' => 'saving the answer', 'id_support' => $this->support->id_support, 'phone' => $this->from, 'message' => $this->body] );

		Crunchbutton_Message_Sms::send([
			'to' => $this->support->phone,
			'message' => $this->admin->firstName() . ': '.$this->message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
		]);

		self::notifyReps($this->admin->firstName() . ' replied to #' . $this->support->id_support . ': ' . $this->message, $support, null, $this->admin);
	}

	public function details() {
		$response = 'From: '.$this->support->phone;
		if ($this->support->id_user) {
			$response .= "\nUser: ".$this->support->user()->name;
		}

		if ($this->support->id_order) {
			$response .= "\nOrder: #".$this->support->id_order;
			$date = new DateTime($this->support->order()->date, new DateTimeZone(c::config()->timezone));
			$date->setTimeZone(new DateTimeZone($this->support->order()->restaurant()->timezone));
			$response .= "\nOrdered @ ".$date->format('n/j g:iA T');

			$response .= "\nRestaurant: ".$this->support->order()->restaurant()->name;

			$response .= "\nDriver: ".$this->support->order()->status()->last()['driver']['name'];
			$response .= "\nStatus: ".$this->support->order()->status()->last()['status'];

			$date = new DateTime($this->support->order()->status()->last()['date'], new DateTimeZone(c::config()->timezone));
			$date->setTimeZone(new DateTimeZone($this->support->order()->restaurant()->timezone));
			$response .= "\nUpdated @ ".$date->format('n/j g:iA T');
		}
		return $response;
	}

	public static function notifyReps($message, $support = null, $media = null, $sender = null) {
		$users = [];
		if($support->id_support && $support->id_community){
			$community = Community::q('SELECT * FROM community WHERE id_community = ?',[$support->id_community])->get(0);
			if($community->id_community){
				$community_cs = $community->workingCommunityCS();
				if($community_cs && count($community_cs) > 0){
					foreach($community_cs as $cs){
						$users[$cs->name] = $cs->phone;
					}
				}
			}
		}

		if(!$users || count($users) == 0){
			$users = Crunchbutton_Support::getUsers();
		}

		if($sender){
			$_phone = $sender->phone;
			if($_phone){
				$_users = [];
				foreach ($users as $name => $phone) {
					if($_phone != $phone){
						$_users[$name] = $phone;
					}
				}
				$users = $_users;
			}
		}
		if(count($users)){
			Crunchbutton_Message_Sms::send([
				'to' => $users,
				'message' => $message,
				'media' => $media,
				'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT
			]);
		}
	}

	public function help($order = null) {
		$response =
			"Support command usage: @".($order ? $order->id_order : 'order')." command|message\n".
			"Commands: \n".
			"    close - close the ticket\n".
			"    info - get info on the ticket\n".
			"Ex:\n".
			"    @".($order ? $order->id_order : '123')." close\n".
			"    @".($order ? $order->id_order : '123')." Hello there!";

		$this->log( [ 'action' => 'help requested', 'invalidOrder' => $invalidOrder ] );
		return $response;
	}

	public function parseBody($body) {
		$body = strtolower($body);

		$verbs = [
			self::ACTION_CLOSE => [ 'close' ],
			self::ACTION_OPEN => [ 'open' ],
			self::ACTION_DETAILS => [ 'info', 'details' ],
			self::ACTION_HELP => [ 'help', 'h', 'info', 'commands', '\?', 'support'],
			self::ACTION_REPLY => [ '.*' ]
		];

		foreach ($verbs[self::ACTION_HELP] as $k => $verb) {
			$help .= ($help ? '$|^' : '').'\/?'.$verb;
		}

		if (preg_match('/^'.$help.'$/',$body)) {
			return ['verb' => self::ACTION_HELP, 'order' => null];
		}

		foreach ($verbs as $verb =>  $verbList) {
			foreach ($verbList as $v) {
				if (preg_match('/^(\@|\#)([0-9]+) \/?('.$v.')$/', $body, $matches)) {
					return ['verb' => $verb, 'support' => $matches[2], 'message' => $matches[3]];
				}
			}
		}

		return false;
	}

	public function log($content) {
		Log::debug( array_merge ( $content, [ 'type' => 'support-sms' ] ) );
	}
}
