<?php

class Crunchbutton_Cron_Job_OrderRules extends Crunchbutton_Cron_Log {

	public function run() {

		$q = '
			select * from `order`
			where date >= now()
			limit 10
		';
		$orders = Order::q($q);

		foreach ($orders as $order) {
			echo 'runing rules for '.$order->id_order."\n";

			$rules = new Crunchbutton_Order_Rules();
			$rules->run($order);
		}

		echo 'done ruling.';

		// it always must call finished method at the end
		$this->finished();
	}
}