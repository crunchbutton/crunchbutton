<?php

class Crunchbutton_Notification_Log extends Cana_Table {
	public function order() {
		return Order::o($this->id_order);
	}
	
	public function tries() {
		return self::q('select * from notification_log where id_order="'.$this->id_order.'"')->count();
	}
	
	public function deleteFromOrder( $id_order ){
		$query = 'DELETE FROM notification_log WHERE id_order = ' . $id_order;
		Cana::db()->query( $query );
	}

	public function notification() {
		return Notification::o($this->id_notification);
	}
	
	public function callback() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback" and `type`="twilio"');

		if ($nl->count() >= c::config()->twilio->maxcallback) {
			$this->status = 'maxcallbackexceeded';
			$this->save();
			
			if (c::env() != 'live') {
				return;
			}

			// Issue #1250 - make Max CB a phone call in addition to a text
			$env = c::env() == 'live' ? 'live' : 'dev';

			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

			$support = c::config()->text;

			$url = 'http://'.c::config()->host_callback.'/api/order/' . $this->id_order . '/maxcalling';

			Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB - starting', 'url' => $url, 'callto'=> $support, 'type' => 'notification' ]);

			// c::timeout(function() use( $support, $twilio, $url ) {
				foreach ( $support as $supportName => $supportPhone ) {
					// Log
					Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB', 'supportPhone' => $supportPhone, 'supportName' => $supportName, 'url' => $url, 'type' => 'notification' ]);

					$call = $twilio->account->calls->create(
						c::config()->twilio->{$env}->outgoingRestaurant,
						'+1'.$supportPhone,
						$url
					);
				}
			// });

			Log::critical([
				'id_notification_log' => $this->id_notification_log,
				'id_notification' => $this->id_notification,
				'id_restaurant' => $this->order()->restaurant()->id_restaurant,
				'restaurant' => $this->order()->restaurant()->name,
				'restaurant_phone' => $this->order()->restaurant()->phone,
				'customer_phone' => $this->order()->phone,
				'customer_name' => $this->order()->name,
				'action' => '#'.$this->id_order.' MAX CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\n C# ".$this->order()->name . ' / ' . $this->order()->phone(),
				'host' => c::config()->host_callback,
				'type' => 'notification'
			]);

		} else {
			$this->queCallback();
		}
	}
	
	public function queCallback() {
		$log = $this;

		c::timeout(function() use($log) {
			$not = $log->notification();
			$order = $log->order();
			$not->send($order);
		}, c::config()->twilio->callbackTime);		
	}

	public function confirm() {
		$nl = Notification_Log::q('select * from notification_log where id_order="'.$this->id_order.'" and status="callback" and `type`="confirm"');

		if ($nl->count() >= c::config()->twilio->maxconfirmback) {
			$this->status = 'maxconfirmbackexceeded';
			$this->save();
			
			if (c::env() != 'live') {
				return;
			}

			// Issue #1250 - make Max CB a phone call in addition to a text
			$env = c::env() == 'live' ? 'live' : 'dev';

			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);

			$support = c::config()->text;

			$url = 'http://'.c::config()->host_callback.'/api/order/' . $this->id_order . '/maxconfirmation';

			Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CB Calling - starting', 'url' => $url, 'callto'=> $support, 'type' => 'notification' ]);

			// c::timeout(function() use( $support, $twilio, $url ) {
			foreach ( $support as $supportName => $supportPhone ) {

				// Log
				Log::debug( [ 'order' => $order->id_order, 'action' => 'MAX CONFIRM CB', 'supportPhone' => $supportPhone, 'supportName' => $supportName, 'url' => $url, 'type' => 'notification' ]);

				$call = $twilio->account->calls->create(
					c::config()->twilio->{$env}->outgoingRestaurant,
					'+1'.$supportPhone,
					$url
				);
			}

			Log::critical([
				'id_order' => $this->id_order, 
				'id_notification_log' => $this->id_notification_log,
				'id_notification' => $this->id_notification,
				'id_restaurant' => $this->order()->restaurant()->id_restaurant,
				'restaurant' => $this->order()->restaurant()->name,
				'restaurant_phone' => $this->order()->restaurant()->phone,
				'customer_phone' => $this->order()->phone,
				'customer_name' => $this->order()->name,
				'action' => '#'.$this->id_order.' MAX CONFIRM CB for '.$this->order()->restaurant()->name."\nR# ".$this->order()->restaurant()->phone()."\nC# ".$this->order()->phone(),
				'host' => c::config()->host_callback,
				'type' => 'notification'
			]);
		} else {

			$this->order()->queConfirm();
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('notification_log')
			->idVar('id_notification_log')
			->load($id);
	}
}