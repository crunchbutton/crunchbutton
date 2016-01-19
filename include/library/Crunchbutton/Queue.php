<?php

class Crunchbutton_Queue extends Cana_Table {

	const TYPE_CLASS_ORDER							= 'Crunchbutton_Queue_Order';
	const TYPE_CLASS_CRON							= 'Crunchbutton_Queue_Cron';
	const TYPE_CLASS_ORDER_RECEIPT					= 'Crunchbutton_Queue_Order_Receipt';
	const TYPE_CLASS_ORDER_RECEIPT_SIGNATURE					= 'Crunchbutton_Queue_Order_ReceiptSignature';
	const TYPE_CLASS_ORDER_CONFIRM					= 'Crunchbutton_Queue_Order_Confirm';
	const TYPE_CLASS_ORDER_PEXCARD_FUNDS			= 'Crunchbutton_Queue_Order_PexCard_Funds';
	const TYPE_CLASS_NOTIFICATION_DRIVER			= 'Crunchbutton_Queue_Notification_Driver';
	const TYPE_CLASS_NOTIFICATION_DRIVER_PRIORITY	= 'Crunchbutton_Queue_Notification_Driver_Priority';
	const TYPE_CLASS_NOTIFICATION_DRIVER_HELPOUT	= 'Crunchbutton_Queue_Notification_Driver_HelpOut';
	const TYPE_CLASS_NOTIFICATION_YOUR_DRIVER		= 'Crunchbutton_Queue_Notification_Your_Driver';
	const TYPE_CLASS_NOTIFICATION_MINUTES_WAY		= 'Crunchbutton_Queue_Notification_Minutes_Way';
	const TYPE_CLASS_SETTLEMENT_DRIVER		= 'Crunchbutton_Queue_Settlement_Driver';
	const TYPE_CLASS_SETTLEMENT_RESTAURANT		= 'Crunchbutton_Queue_Settlement_Restaurant';
	const TYPE_CLASS_RESTAURANT_TIME		= 'Crunchbutton_Queue_Restaurant_Time';
	const TYPE_CLASS_EVENT_EMIT		= 'Crunchbutton_Queue_Event_Emit';

	const TYPE_ORDER						= 'order';
	const TYPE_CRON						= 'cron';
	const TYPE_ORDER_RECEIPT				= 'order-receipt';
	const TYPE_ORDER_RECEIPT_SIGNATURE				= 'order-receipt-signature';
	const TYPE_ORDER_CONFIRM				= 'order-confirm';
	const TYPE_ORDER_PEXCARD_FUNDS			= 'order-pexcard-funds';
	const TYPE_NOTIFICATION_DRIVER			= 'notification-driver';
	const TYPE_NOTIFICATION_DRIVER_PRIORITY = 'notification-driver-priority';
	const TYPE_NOTIFICATION_DRIVER_HELPOUT = 'notification-driver-helpout';
	const TYPE_NOTIFICATION_YOUR_DRIVER		= 'notification-your-driver';
	const TYPE_NOTIFICATION_MINUTES_WAY		= 'notification-minutes-way';
	const TYPE_SETTLEMENT_DRIVER		= 'settlement-driver';
	const TYPE_SETTLEMENT_RESTAURANT		= 'settlement-restaurant';
	const TYPE_RESTAURANT_TIME		= 'restaurant-time';
	const TYPE_EVENT_EMIT		= 'event-emit';

	const STATUS_NEW		= 'new';
	const STATUS_SUCCESS	= 'success';
	const STATUS_FAILED		= 'failed';
	const STATUS_RUNNING	= 'running';
	const STATUS_STOPPED	= 'stopped';


	public static function process($all = false) {

		if (!$all) {
			$allQuery = ' and (date_run<now() or date_run is null)';
		}

		// use dbwrite so there is no lag
		$qq = new Crunchbutton_Queue;
		$qq->dbWrite(c::dbWrite());
		$queue = $qq->q('select * from queue where status=?'.$allQuery.' order by date_run asc', [self::STATUS_NEW]);
		$count = $queue->count();
		//$processid = uniqid();

		foreach ($queue as $q) {
			echo '  Starting #'.$q->id_queue. '...';

			$q->status = self::STATUS_RUNNING;
			$q->date_start = date('Y-m-d H:i:s');

			$res = c::dbWrite()->query(
				'update queue set status=?, date_start=? where id_queue=? and status=?',
				[$q->status, $q->date_start, $q->id_queue, self::STATUS_NEW]
			);

			if (!$res->rowCount()) {
				echo "skipping\n";
				$count--;
				continue;
			}

			register_shutdown_function(function() use ($q) {
				$error = error_get_last();
				if ($error['type'] == E_ERROR) {
					$q->data = json_encode($error);
					$q->date_end = date('Y-m-d H:i:s');
					$q->status = self::STATUS_FAILED;
					$q->save();
				}
			});

			$queue_type = $q->queue_type()->type;

			// Legacy
			if( !$queue_type && $q->type ){
				$queue_type = $q->type;
			}

			$type = 'TYPE_CLASS_'.str_replace('-','_',strtoupper($queue_type));
			$class = constant('self::'.$type);
			if (!$class) {
				$q->status = self::STATUS_FAILED;
				$q->date_end = date('Y-m-d H:i:s');
				$q->data = 'Invalid class type of: '.$queue_type;
				continue;
			}

			$q = new $class($q->properties());

			$res = $q->run();

			register_shutdown_function(function(){});

			if ($res !== false) {
				// not async
				$q->complete($res);
			}
		}
		return $count;
	}

	public function queue_type(){
		if( !$this->_queue_type ){
			$this->_queue_type = Crunchbutton_Queue_Type::o( $this->id_queue_type );
		}
		return $this->_queue_type;
	}

	// dump the que and do nothing
	public static function clean() {
		c::dbWrite()->exec('update queue set status=?', [self::STATUS_STOPPED]);
	}

	// run the entire que until its empty
	public static function end() {
		self::process(true);
	}

	public function complete($status = self::STATUS_SUCCESS) {
		$this->status = $status;
		$this->data = null;
		$this->date_end = date('Y-m-d H:i:s');
		$this->save();
		echo $status."\n";
	}

	public function order() {
		return Order::o($this->id_order);
	}

	public function cron(){
		return Crunchbutton_Cron_Log::o( $this->id_cron_log );
	}

	public function driver() {
		return Admin::o($this->id_admin);
	}

	public function restaurant() {
		return Restaurant::o($this->id_restaurant);
	}

	public static function create($params = []) {

		// backwards compatable so i dont break things
		if ($params['date_start']) {
			$params['date_run'] = $params['date_start'];
			unset($params['date_start']);
		}
		if (!$params['date_run']) {
			$params['date_run'] = date('Y-m-d H:i:s');
		}

		$type = Crunchbutton_Queue_Type::byType( $params[ 'type' ] );

		if( !$type ){
			return;
		}

		$params['id_queue_type'] = $type->id_queue_type;

		if ($params['seconds']) {
			$params['date_run'] = date('Y-m-d H:i:s', time() + $params['seconds']);
			unset($params['seconds']);
		}

		$params['status'] = self::STATUS_NEW;

		$q = new Crunchbutton_Queue($params);
		$q->save();

		return $q;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('queue')
			->idVar('id_queue')
			->load($id);
	}
}
