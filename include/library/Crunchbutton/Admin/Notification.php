<?php

class Crunchbutton_Admin_Notification extends Cana_Table {

    // For logistics: the mechanisms for the first message are different from those of subsequent messages
    //  so one needs to be careful.

	const TYPE_SMS = 'sms';
	const TYPE_DUMB_SMS = 'sms-dumb';
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_URL = 'url';
	const TYPE_FAX = 'fax';
	const TYPE_PUSH_IOS = 'push-ios';
	const TYPE_PUSH_ANDROID = 'push-android';
	const REPS_COCKPIT = 'https://cockpit.la/';

	const IS_ENABLE_KEY = 'notification-admin-is-enable';
	const IS_ENABLE_TO_TAKEOUT_KEY = 'notification-admin-is-enable-takeout';

	const IS_ENABLE_DEFAULT = true;
	const IS_ENABLE_TO_TAKEOUT_DEFAULT = false;

	const REPS_NONE_WORKING_GROUP_NAME_KEY = 'reps-none-working-group-name';

	const WAIT_BUFFER = 300; // seconds
	const FIRST_DELAY = 300; //seconds
	const SUBSEQUENT_DELAY = 180; //seconds

	const PRIORITY_MESSAGES_INDEX = 0;
	const PRIORITY_MSG_NO_PRIORITY = 0;
	const PRIORITY_MSG_PRIORITY = 1;
	const PRIORITY_MSG_INACTIVE_DRIVER_PRIORITY = 2;
	const PRIORITY_MSG_SECOND_PLACE_DRIVER_PRIORITY = 3;

	public static function getSecondsDelayFromAttemptCount($numAttempts){
		if ($numAttempts == 0) {
			$retval = self::FIRST_DELAY;
		} else{
			$retval = self::SUBSEQUENT_DELAY;
		}
		return $retval;
	}

	public static function sendAndRegisterAllPriorityNotifications($driver, $order, $numAttempts) {
		$id_admin = $driver->id_admin;
        $hasUnexpired = Crunchbutton_Admin_Notification_Log::adminHasUnexpiredNotification($order->id_order, $id_admin);

        if (!$hasUnexpired) {
            $hostname = gethostname();
            $pid = getmypid();
            $ppid = NULL;
//			$ppid = posix_getppid();
            if (is_null($hostname)) {
                $hostname = "NA";
            }
            if (is_null($pid)) {
                $pid = "NA";
            }
            if (is_null($ppid)) {
                $ppid = "NA";
            }
            foreach ($driver->activeNotifications() as $adminNotification) {
//                print "Check here";
                $adminNotification->send($order, $numAttempts);
                $message = '#' . $order->id_order . ' attempts: ' . $numAttempts . ' sending driver notification to ' . $driver->name . ' #' . $adminNotification->value;
                Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver2',
                    'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);
            }
            $seconds = self::getSecondsDelayFromAttemptCount($numAttempts);
            // Note that the send happens before the register for counting purposes
            Crunchbutton_Admin_Notification_Log::registerWithAdminForLogistics($order->id_order, $id_admin, $seconds, $numAttempts);
        }
	}


	public function handlePriorityLogisticsNotification($order, $sorted_order_priorities) {
		// Order priorities should be sorted by seconds delay descending∂

		// In case drivers bailed out after priorities were handed out
		$drivers = $order->getDriversToNotify();
        $numDrivers = $drivers->count();
        Log::debug(['order' => $order->id_order, 'action' => "handlePriorityLogisticsNotification", 'type' => 'delivery-driver2',
            'numDriversToNotify' => $numDrivers]);
		if ($drivers) {
			$driverDict = [];
			foreach ($drivers as $driver) {
				$driverDict[$driver->id_admin] = $driver;
			}
			$minAttemptsForLowestPriority = null;
			$secondsDelayForLowestPriority = null;
			foreach ($sorted_order_priorities as $op) {
				$id_admin = $op->id_admin;
				if (array_key_exists($id_admin, $driverDict)) {
                    $driver = $driverDict[$id_admin];
					$secondsDelay = $op->seconds_delay;

					$attempts = Crunchbutton_Admin_Notification_Log::sortedAttemptsWithAdmin($order->id_order, $id_admin);
					if ($attempts->count() == 0) {
						$orderTS = strtotime($order->date);
						$nowTS = time();
						// This is in case things get messed-up/super-delayed in the system somehow
						// Just send immediately
                        Log::debug(['order' => $order->id_order, 'id_admin' => $id_admin, 'action' => "handlePriorityLogisticsNotification", 'type' => 'delivery-driver2',
                            'stage' => "Messed up - super delayed"]);
                        $maxTime = self::WAIT_BUFFER + $orderTS;
						if ($nowTS > $maxTime) {
							// Send 1st notification
							self::sendAndRegisterAllPriorityNotifications($driver, $order, 0);
						}
					} else {
						$numPriorAttempts = 0;
						$gotFirst = false;
						$expired = false;
						foreach ($attempts as $attempt) {
							if (!$gotFirst) {
								// Get the info from the last attempt;
								$gotFirst = true;
								$attemptTS = strtotime($attempt->date);
								$nowTS = time();
								if ($attemptTS <= $nowTS) {
									$numPriorAttempts++;
									$expired = true;
								}
							} else {
								$numPriorAttempts++;
							}

						}
                        Log::debug(['order' => $order->id_order, 'id_admin' => $id_admin, 'action' => "handlePriorityLogisticsNotification", 'type' => 'delivery-driver2',
                            'numPriorAttempts' => $numPriorAttempts]);
						if (is_null($secondsDelayForLowestPriority)) {
							$secondsDelayForLowestPriority = $secondsDelay;
							$minAttemptsForLowestPriority = $numPriorAttempts;

						} else if ($secondsDelayForLowestPriority==$secondsDelay) {
							if ($numPriorAttempts < $minAttemptsForLowestPriority) {
								$minAttemptsForLowestPriority = $numPriorAttempts;
							}
						}
						$driver = $driverDict[$id_admin];

						if (is_null($minAttemptsForLowestPriority) || $numPriorAttempts < $minAttemptsForLowestPriority) {
							$minAttemptsForLowestPriority = $numPriorAttempts;
						}
						if ($expired && $numPriorAttempts < 3) {
							self::sendAndRegisterAllPriorityNotifications($driver, $order, $numPriorAttempts);
						} else if ($expired && $numPriorAttempts == 3) {
                            // We only register after this
							Crunchbutton_Admin_Notification_Log::registerWithAdminForLogistics($order->id_order, $driver->id_admin, 0, $numPriorAttempts);
						}
					}
				}

			}
            Log::debug(['order' => $order->id_order, 'action' => "handlePriorityLogisticsNotification", 'type' => 'delivery-driver2',
                'minAttemptsForLowestPriority' => $minAttemptsForLowestPriority]);
			if ($minAttemptsForLowestPriority == 3) {
				$this->alertDispatch($order, $drivers);
			}
		} else{
			$restaurant = $order->restaurant()->name;
			$community = $order->restaurant()->communityNames();
			if ($community != '') {
				$restaurant .= ' (' . $order->restaurant()->community . ')';
			}
			$message = '#' . $order->id_order . ' there is no drivers to get the order - ' . $restaurant;
			Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver2']);
			echo $message . "\n";
			// Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
		}
	}

	public function resendNotification(){
		// This operates like a static function.  No order or admin associated with it.

        $hostname = gethostname();
        $pid = getmypid();
        $ppid = NULL;
//			$ppid = posix_getppid();
        if (is_null($hostname)) {
            $hostname = "NA";
        }
        if (is_null($pid)) {
            $pid = "NA";
        }
        if (is_null($ppid)) {
            $ppid = "NA";
        }
		$type_admin = Crunchbutton_Notification::TYPE_ADMIN;
		$type_delivery = Crunchbutton_Order::SHIPPING_DELIVERY;
		$orderFromLast = ' 3 HOUR';
        Log::debug(['action' => "AdminNotification::resendNotification", 'type' => 'delivery-driver',
            'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);

		$query = "SELECT * FROM
								`order` o
							WHERE
								o.delivery_type = '{$type_delivery}'
									AND
								o.delivery_service = true
									AND
								o.date > DATE_SUB(NOW(), INTERVAL {$orderFromLast} )
									AND
								o.date < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
									AND
									 o.name not like '%test%'

							ORDER BY o.id_order ASC";
// Uncomment this and comment out above if you want test community to be able to deal with follow-up driver notifications
//        $query = "SELECT * FROM
//								`order` o
//							WHERE
//								o.delivery_type = '{$type_delivery}'
//									AND
//								o.delivery_service = true
//									AND
//								o.date > DATE_SUB(NOW(), INTERVAL {$orderFromLast} )
//									AND
//								o.date < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
//							ORDER BY o.id_order ASC";

		$orders = Crunchbutton_Order::q($query);

		$message = 'working with '.$orders->count().' orders';

//		Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver' ] );

		echo $message ."\n";

		if ( $orders->count() > 0 ) {

			foreach ( $orders as $order ) {
				if( !$order->wasAcceptedByRep() && !$order->wasCanceled() ) {
                    Log::debug(['order' => $order->id_order, 'action' => "Resend start", 'type' => 'delivery-driver',
                        'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);

					$hasDriversWorking = false;

					$order_priorities = Crunchbutton_Order_Priority::getOrderedOrderPriorities($order->id_order);
          $order_priorities_count = $order_priorities->count();
					if ($order_priorities->count() == 0) {

						$attempts = intval(Crunchbutton_Admin_Notification_Log::attemptsWithNoAdmin($order->id_order));

						$message = '#' . $order->id_order . ' was not accepted - attempts ' . $attempts;
						Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver',
                            'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);
						echo $message . "\n";

						if ($attempts > 2) {
							$message = '#' . $order->id_order . ' CS was already texted about it - attempts ' . $attempts;
							Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver']);
							echo $message . "\n";
							continue;
						} else if ($attempts == 2) {
							// aqui
							// More info: https://github.com/crunchbutton/crunchbutton/issues/2352#issuecomment-34780213
							$this->alertDispatch($order);
							Crunchbutton_Admin_Notification_Log::register($order->id_order, ' Notification::resendNotification');
							continue;
						} else {

							$drivers = $order->getDriversToNotify();
							if ($drivers) {
								foreach ($drivers as $driver) {
									foreach ($driver->activeNotifications() as $adminNotification) {
										$adminNotification->send($order, $attempts);
										$hasDriversWorking = true;
										$message = '#' . $order->id_order . ' attempts: ' . $attempts . ' sending driver notification to ' . $driver->name . ' #' . $adminNotification->value;
										Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver']);
										echo $message . "\n";
									}
								}
								Crunchbutton_Admin_Notification_Log::register($order->id_order, ' Notification::resendNotification');
							}

							if (!$hasDriversWorking) {
								$restaurant = $order->restaurant()->name;
								$community = $order->restaurant()->communityNames();
								if ($community != '') {
									$restaurant .= ' (' . $order->restaurant()->community . ')';
								}
								$message = '#' . $order->id_order . ' there is no drivers to get the order - ' . $restaurant;
								Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver']);
								echo $message . "\n";
								// Crunchbutton_Admin_Notification::warningAboutNoRepsWorking( $order );
							}
						}
					} else{
//                        Log::debug(['order' => $order->id_order, 'action' => "Handling priority logistics", 'type' => 'delivery-driver',
//                            'hostname' => $hostname, 'pid' => $pid, 'ppid' => $ppid]);
						$this->handlePriorityLogisticsNotification($order, $order_priorities);
					}

				}
                else {
					$message = '#' . $order->id_order . ' was accepted or canceled';
					Log::debug(['order' => $order->id_order, 'action' => $message, 'type' => 'delivery-driver']);
					echo $message . "\n";
				}

			}
		}
	}


	public function alertDispatch( Crunchbutton_Order $order, $drivers=null) {

		$env = c::getEnv();
		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$message = 'Reps failed to pickup order #' . $order->id_order . '. Restaurant ' . $order->restaurant()->name . ' / Customer ' . $order->name . ' https://cockpit.la/' . $order->id_order;

		// Get drivers name
		// Get drivers name
		if(is_null($drivers) ){
			$drivers = Crunchbutton_Community_Shift::driversCouldDeliveryOrder( $order->id_order );
		}

        $driversToNotify = [];
		if( $drivers ){
			foreach( $drivers as $driver ){
				foreach( $driver->activeNotifications() as $adminNotification ){
					$driversToNotify[ $driver->id_admin ] = $driver->name . ': ' . $driver->phone();
				}
			}
		}
		$drivers_list = "";
		$commas = "";
		foreach( $driversToNotify as $key => $val ){
			$drivers_list .= $commas . $val;
			$commas = "; ";
		}

		$sms_message = Crunchbutton_Message_Sms::greeting() . '#'.$order->id_order.' sms: reps failed to pickup order';
		$sms_message .= "\n";
		$sms_message .= "R: " . $order->restaurant()->name;
		if( $order->restaurant()->community && $order->restaurant()->community != '' ){
			$sms_message .= ' (' . $order->restaurant()->community . ')';
		}
		$sms_message .= "\n";
		$sms_message .= "C: " . $order->name;

		if( $drivers_list != '' ){
			$sms_message .= "\nD: " . $drivers_list;
		}

		// Reps failed to pickup order texts changes #2802
		Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $sms_message, 'bubble' => true ] );

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => $sms_message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
		]);

		echo $sms_message."\n";
//		Log::debug( [ 'order' => $order->id_order, 'action' => $sms_message, 'num' => $a->txt, 'message' => $sms_message, 'type' => 'delivery-driver' ]);


		/*
		Removed the phone call for while - asked by David 2/23/2014
		$num = $a->getPhoneNumber();
		if( $num ){

			$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/pick-up-fail';
			$message = '#'.$order->id_order.' call: reps failed to pickup order url: ' . $url;
			$message .= "\n";
			$message .= $order->restaurant()->name;
			if( $order->restaurant()->community && $order->restaurant()->community != '' ){
				$message .= ' (' . $order->restaurant()->community . ')';
			}

			echo $message."\n";
			Log::debug( [ 'order' => $order->id_order, 'action' => $message, 'num' => $num, 'type' => 'delivery-driver' ]);
			$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
			$call = $twilio->account->calls->create(
				c::config()->twilio->{$env}->outgoingDriver,
				'+1'.$num,
				$url
			);
		}
		*/

	}

	public function sendPriority( Crunchbutton_Order $order, $priorityMsgType ){

		switch ( $this->type ) {

			case Crunchbutton_Admin_Notification::TYPE_SMS:
				$res = $this->sendSms( $order, $this->getSmsMessage($order, self::PRIORITY_MESSAGES_INDEX, 'sms', $priorityMsgType));
				break;

			case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS:
				$res = $this->sendPushIos( $order, $this->getSmsMessage($order, self::PRIORITY_MESSAGES_INDEX, 'push', $priorityMsgType));
				break;

			case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID:
				$res = $this->sendPushAndroid( $order, $this->getSmsMessage($order, self::PRIORITY_MESSAGES_INDEX, 'push', $priorityMsgType));
				break;
		}
	}

	public function calculateAttempts($order) {
		// We don't need logic to check if something is wrong with the queue system, since this won't
		//  even run if there are issues with the queue system
		$curCommunity = $order->community();
		if (!is_null($curCommunity) && !is_null($curCommunity->delivery_logistics) && ($curCommunity->delivery_logistics != 0)) {
			$admin = $this->admin();
			if (is_null($admin)){
				$attempts = Crunchbutton_Admin_Notification_Log::attemptsWithNoAdmin($order->id_order);
			} else {
                // We need the cutoff here because for the original message that is sent, there can be a delay due to the queue
                //  running after message is registered
				$attempts = Crunchbutton_Admin_Notification_Log::attemptsWithAdminAndCutoff($order->id_order, $admin->id_admin);
			}
		} else {
			$attempts = Crunchbutton_Admin_Notification_Log::attemptsWithNoAdmin($order->id_order);
		}
		return $attempts;
	}


	public function send( Crunchbutton_Order $order, $attempts=null ) {

		$env = c::getEnv();

		if (is_null($attempts)) {
			$attempts = $this->calculateAttempts($order);
		}
		if( $env != 'live' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin at DEV - not sent', 'notification_type' => $this->type, 'value'=> $this->value, 'attempt' => $attempts, 'type' => 'delivery-driver' ]);
			// return;
		}

		$is_enable = ( !is_null( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_KEY ) ) ? ( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_KEY ) == '1' ) : Crunchbutton_Admin_Notification::IS_ENABLE_DEFAULT );
		if( !$is_enable ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin is disabled', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);
			return null;
		}

		$is_enable_takeout = ( !is_null( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) ) ? ( $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) == '1' ) : Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_DEFAULT );
		if( $order->delivery_type == Crunchbutton_Order::SHIPPING_TAKEOUT && $this->getSetting( Crunchbutton_Admin_Notification::IS_ENABLE_TO_TAKEOUT_KEY ) != '1' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'notification to admin to TAKEOUT is disabled', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);
			return null;
		}

		Log::debug( [ 'order' => $order->id_order, 'attempts' => $attempts, 'action' => 'notification to admin starting', 'notification_type' => $this->type, 'value'=> $this->value, 'type' => 'delivery-driver' ]);

		if( $attempts == 0 ){
			switch ( $this->type ) {
				case Crunchbutton_Admin_Notification::TYPE_FAX :
					$res = $this->sendFax( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_SMS :
					$res = $this->sendSms( $order, $this->getSmsMessage($order, 1, 'sms'));
					break;

				case Crunchbutton_Admin_Notification::TYPE_DUMB_SMS :
					$res = $this->sendDumbSms( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_PHONE :
					$res = $this->phoneCall( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_EMAIL :
					$res = $this->sendEmail( $order );
					break;

				case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS :
					$res = $this->sendPushIos( $order, $this->getSmsMessage($order, 1, 'push'));
					break;

				case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID :
					$res = $this->sendPushAndroid( $order, $this->getSmsMessage($order, 1, 'push'));
					break;
			}
		} else if( $attempts >= 1 ){
			$admin = $this->admin();
			switch ( $attempts ) {
				case 1:
					$shift = Crunchbutton_Community_Shift::currentDriverShift($admin->id_admin);
					if ($shift->id_community_shift) {
						$shiftDateStart = $shift->dateStart(c::config()->timezone);
					}

					if ($shiftDateStart && ($order->date() < $shiftDateStart)) {
						$c = 1;
					} else {
						$c = 2;
					}

					switch ( $this->type ) {

						case Crunchbutton_Admin_Notification::TYPE_PUSH_IOS :
							$res = $this->sendPushIos( $order, $this->getSmsMessage($order, $c, 'push'));
							break;

						case Crunchbutton_Admin_Notification::TYPE_PUSH_ANDROID :
							$res = $this->sendPushAndroid( $order, $this->getSmsMessage($order, $c, 'push'));
							break;

						default:
						case Crunchbutton_Admin_Notification::TYPE_SMS :
							$res = $this->sendSms( $order, $this->getSmsMessage($order, $c, 'sms'));
							break;
					}

					break;
				/*
				case 2:
					$phoneNumber = $admin->getPhoneNumber();
					if( $phoneNumber ){
						$call = 'driver-second-call-warning';
						$env = c::getEnv();
						$num = $phoneNumber;
						$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/'.$call;
						Log::debug( [ 'order' => $order->id_order, 'action' => 'send call to admin', 'num' => $num, 'host' => $this->host_callback(), 'env' => $env, 'url' => $url, 'type' => 'admin_notification' ]);
						$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
						$call = $twilio->account->calls->create(
							c::config()->twilio->{$env}->outgoingDriver,
							'+1'.$num,
							$url,
							[ 'IfMachine' => 'Hangup' ]
						);
					}
					break;
					*/
			}
		}

		return $res;
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function warningAboutNoRepsWorking( $order ){

		$env = c::getEnv();

		if( $env != 'live' ){
			Log::debug( [ 'order' => $order->id_order, 'action' => 'warningAboutNoRepsWorking at DEV - not sent','type' => 'admin_notification' ]);
			return;
		}

		//spam bug - let it here for while @pererinha
		if( $order->status() && $order->status()->last() ){
			$status = $order->status()->last()[ 'status' ];
			if( $status != 'new' ){
				Log::debug( [ 'order' => $order->id_order, 'status'=> $status, 'action' => 'warningAboutNoRepsWorking odd spamming bug - not sent','type' => 'watching' ]);
				return;
			}
		}



		$group = Group::byName( Crunchbutton_Config::getVal( Crunchbutton_Admin_Notification::REPS_NONE_WORKING_GROUP_NAME_KEY ) );
		$users = Crunchbutton_Admin::q( "SELECT a.* FROM admin a INNER JOIN admin_group ag ON ag.id_admin = a.id_admin AND ag.id_group = {$group->id_group}" );
		$twilio = new Services_Twilio( c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token );

		if( $order->restaurant()->community && $order->restaurant()->community != '' ){
			$community = '(' . $order->restaurant()->community . ') ';
		} else {
			$community = '';
		}

		$message = "No drivers for O#{$order->id_order} \nR: {$order->restaurant()->name} {$community}/ {$order->restaurant()->phone()} \nC: {$order->name} / {$order->phone()}";

		// Make these notifications pop up on support on cockpit #3008
		Crunchbutton_Support::createNewWarning( [ 'id_order' => $order->id_order, 'body' => $message ] );

		Crunchbutton_Message_Sms::send([
			'to' => Crunchbutton_Support::getUsers(),
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_SUPPORT_WARNING
		]);
	}

	public function host_callback(){
		if( !c::config()->host_callback ){
			if( c::getEnv() == 'live' ){
				return '_DOMAIN_';
			} else if( c::getEnv() == 'beta' ){
				return 'beta.crunchr.co';
			} else {
				return $_SERVER['HTTP_HOST'];
			}
		}
		return c::config()->host_callback;
	}

	public function sendFax( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$fax = $this->value;
		$cockpit_url = self::REPS_COCKPIT . $order->id_order;

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send fax to admin', 'fax' => $fax, 'host' => $this->host_callback(), 'type' => 'admin_notification' ]);


		$admin = $this->admin();
		$mail = new Email_Order( [	'order' => $order,
																'cockpit_url' => $cockpit_url,
																'show_credit_card_tips' => $admin->showCreditCardTips(),
																'show_delivery_fees' => $admin->showDeliveryFees(),
															] );

		$temp = tempnam('/tmp','fax');

		file_put_contents($temp, $mail->message());
		rename($temp, $temp.'.html');

		$fax = new Phaxio( [ 'to' => $fax, 'file' => $temp.'.html' ] );

		unlink($temp.'.html');
	}

	public function phoneCall( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$num = $this->value;
		$url = 'http://'.$this->host_callback().'/api/order/'.$order->id_order.'/sayorderadmin';

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send call to admin', 'num' => $num, 'host' => $this->host_callback(), 'env' => $env, 'url' => $url, 'type' => 'admin_notification' ]);

		$twilio = new Services_Twilio(c::config()->twilio->{$env}->sid, c::config()->twilio->{$env}->token);
		$call = $twilio->account->calls->create(
			c::config()->twilio->{$env}->outgoingDriver,
			'+1'.$num,
			$url,
			[ 'IfMachine' => 'Hangup' ]
		);

	}

	public function sendDumbSms( Crunchbutton_Order $order ){

		$env = c::getEnv();

		$sms = $this->value;

		$admin = $this->admin();
		if( $admin->id_admin ){
			$name = $admin->firstName();
		} else {
			$name = '';
		}

		$message = Crunchbutton_Message_Sms::greeting( $name ) . $order->message( 'sms-driver' );

		Crunchbutton_Message_Sms::send([
			'to' => $sms,
			'from' => 'driver',
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_ORDER
		]);
	}

	public function sendSms( Crunchbutton_Order $order, $message){

		$sms = $this->value;

		$ret = Crunchbutton_Message_Sms::send([
			'to' => $sms,
			'from' => 'driver',
			'message' => $message,
			'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_ORDER
		]);

		Log::debug( [ 'order' => $order->id_order, 'action' => 'send sms to admin', 'sms' => $sms, 'message' => $message, 'type' => 'admin_notification' ]);

		return $ret;
	}

	public function sendEmail( Crunchbutton_Order $order ){

		$env = c::getEnv();
		$mail = $this->value;
		$admin = $this->admin();
		Log::debug( [ 'order' => $order->id_order, 'action' => 'send mail to admin', 'mail' => $mail, 'type' => 'admin_notification' ]);
		$cockpit_url = self::REPS_COCKPIT . $order->id_order;

		$mail = new Email_Order( [	'order' => $order,
																'email' => $mail,
																'cockpit_url' => $cockpit_url,
																'show_credit_card_tips' => $admin->showCreditCardTips(),
																'show_delivery_fees' => $admin->showDeliveryFees(),
															] );
		$mail->send();
	}

	public function spellOutURL( $id_order ) {
		$cockpit_url = Crunchbutton_Admin_Notification::REPS_COCKPIT . $id_order;
		$name = preg_replace('/^[0-9]+ (.*)$/i','\\1',$cockpit_url);
		$spaceName = '';

		for ($x=0; $x<strlen($name); $x++) {
			$letter = strtolower($name{$x});
			$addPause = false;
			switch ($letter) {
				case ' ':
				case ',':
				case "\n":
					$addPause = true;
					break;
				case 'c':
					$letter = 'see.';
					break;
				case '.':
					$letter = 'dot.';
					break;
				case ':':
					$letter = 'colon.';
					break;
				case '/':
					$letter = 'slash.';
					break;
				default:
					break;
			}
			if ($addPause) {
				$spaceName .= '<Pause length="1" />';
			}
			$spaceName .= '<Say voice="'.c::config()->twilio->voice.'"><![CDATA['.$letter.']]></Say><Pause length="1" />';
		}
		return $spaceName;
	}

	private function loadSettings(){
		$configs = Crunchbutton_Config::q( "SELECT * FROM config WHERE `key` LIKE 'notification-admin-%'" );
		foreach ( $configs as $config ) {
			$this->_config[ $config->key ] = $config->value;
		}
	}

	public function getSetting( $key ){
		if( !$this->_config ){
			$this->loadSettings();
		}
		return $this->_config[ $key ];
	}

	public function getPendingOrderCount() {
		// @todo: this is not working right
		$orders = Crunchbutton_Order::q('SELECT * FROM `order` o WHERE o.delivery_type = "'.Crunchbutton_Order::SHIPPING_DELIVERY.'" AND o.delivery_service = true AND o.date > DATE_SUB(NOW(), INTERVAL 3 HOUR ) AND o.date < DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY o.id_order ASC');
		return $orders->count();
	}

	public function sendPushIos($order, $message) {
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => $this->value,
			'message' => $message,
			'count' => 1,
			'id' => 'order-'.$order->id,
			'category' => 'order-new-test',
			'sound' => Crunchbutton_Message_Push_Ios::SOUND_NEW_ORDER,
			'showInForeground' => true,
			'link' => '/drivers/order/'.$order->id,
			'app' => 'cockpit'
		]);

		return $r;
	}

	public function sendPushAndroid($order, $message) {

		$r = Crunchbutton_Message_Push_Android::send([
			'to' => $this->value,
			'message' => $message,
			'title' => 'Cockpit New Order',
			'count' => 1,
			'id' => 'order-'.$order->id,
			'sound' => Crunchbutton_Message_Push_Ios::SOUND_NEW_ORDER,
			'showInForeground' => true,
			'link' => '/drivers/order/'.$order->id,
			'app' => 'cockpit'
		]);

		return $r;
	}

	public function getSmsMessage($order, $count = 1, $type = 'push', $priorityMsgType = self::PRIORITY_MSG_NO_PRIORITY) {


		if ($priorityMsgType == self::PRIORITY_MSG_NO_PRIORITY) {
			switch ($count) {


				default:
				case 1:
					switch ($type) {
						default:
						case 'sms':
							$message = Crunchbutton_Message_Sms::greeting($this->admin()->id_admin ? $this->admin()->firstName() : '');
							$message .= self::REPS_COCKPIT . $order->id_order . "\n";
							$message .= $order->message('sms-admin');
							break;
						case 'push':
							$message = '#' . $order->id . ': ' . $order->user()->name . ' has placed an order to ' . $order->restaurant()->name . '.';
							break;
					}

					break;

				case 2:
					switch ($type) {
						default:
						case 'sms':
							$first_name = Crunchbutton_Message_Sms::greeting($this->admin()->firstName());
							$message = $first_name . 'Remember: plz ACCEPT this order https://cockpit.la/' . $order->id_order . '. Otherwise our customers won\'t know what\'s up and we might have to shut down the community!';
							break;
						case 'push':
							$message = 'Please ACCEPT order #' . $order->id . ' from ' . $order->user()->name . ' to ' . $order->restaurant()->name . '.';
							break;
					}


					break;
			}
		} else {
			switch ($priorityMsgType) {
				// priority messages
				case self::PRIORITY_MSG_PRIORITY:
					switch ($type) {
						default:
						case 'sms':
							$message = "New priority order for you";
							$message .= Crunchbutton_Message_Sms::endGreeting($this->admin()->id_admin ? $this->admin()->firstName() : '', "!", "\n");
							$message .= "Sent to YOU 1st. Accept ASAP before others see it!\n";
							$message .= self::REPS_COCKPIT . $order->id_order . "\n";
							$message .= $order->message('sms-driver-priority') . "\n";
							break;
						case 'push':
							$message = "New priority order for you";
							$message .= Crunchbutton_Message_Sms::endGreeting($this->admin()->id_admin ? $this->admin()->firstName() : '', "!");
							$message .= "Sent to YOU 1st. Accept ASAP before others see it!";
							$message = '#' . $order->id . ': ' . $order->user()->name . ' has placed an order to ' . $order->restaurant()->name . '.';
							break;

					}
					break;
				case self::PRIORITY_MSG_INACTIVE_DRIVER_PRIORITY:
					switch ($type) {
						default:
						case 'sms':
							$message = "Hey, what's up?  You're MIA! :(";
							$message = "We've got a new priority order for you";
							$message .= Crunchbutton_Message_Sms::endGreeting($this->admin()->id_admin ? $this->admin()->firstName() : '', ".", "");
							$message .= "We're also going to let another driver see it though.\n";
							$message .= "Please let us know what's up!\n";
							$message .= self::REPS_COCKPIT . $order->id_order . "\n";
							$message .= $order->message('sms-driver-priority') . "\n";
							break;
						case 'push':
							$message = "New priority order!  We're letting another driver see it too since it seems like you're MIA :(";
							$message = '#' . $order->id . ': ' . $order->user()->name . ' has placed an order to ' . $order->restaurant()->name . '.';
							break;

					}
					break;

				case self::PRIORITY_MSG_SECOND_PLACE_DRIVER_PRIORITY:
					switch ($type) {
						default:
						case 'sms':
							$message = "You have a priority order";
							$message .= Crunchbutton_Message_Sms::endGreeting($this->admin()->id_admin ? $this->admin()->firstName() : '', "!", "\n");
							$message .= "Accept ASAP!\n";
							$message .= self::REPS_COCKPIT . $order->id_order . "\n";
							$message .= $order->message('sms-driver-priority') . "\n";
							break;
						case 'push':
							$message = "You have a priority order.  Accept ASAP!";
							$message = '#' . $order->id . ': ' . $order->user()->name . ' has placed an order to ' . $order->restaurant()->name . '.';
							break;

					}
					break;
			}
		}
		return $message;
	}

	public function save($new = false) {
		if ($this->type == 'sms' || $this->type == 'fax') {
			$this->value = Phone::clean($this->value);
		}
		return parent::save();
	}

	public static function adminHasNotification( $id_admin, $type ){
		if( $id_admin && $type ){
			$notification = Crunchbutton_Admin_Notification::q( 'SELECT * FROM admin_notification WHERE id_admin = ? AND type = ? AND active = 1 LIMIT 1', [ $id_admin, $type ] )->get( 0 );
			if( $notification->id_admin_notification ){
				return true;
			}
		}
		return false;

	}

	public function __construct($id = null) {
		$this->loadSettings();
		parent::__construct();
		$this
			->table('admin_notification')
			->idVar('id_admin_notification')
			->load($id);
	}
}
