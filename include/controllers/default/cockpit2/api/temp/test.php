<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

public function init() {
		echo '<pre>' . "\n";
		$orders = Order::q('select * from `order` where campus_cash = 1 and preordered = 1');
		foreach($orders as $order){
			$oc = Order_Change::q( 'SELECT oc.*, ocs.id_order FROM order_change oc INNER JOIN order_change_set ocs ON ocs.id_order_change_set = oc.id_order_change_set  WHERE ocs.id_order = ? AND oc.field = "campus_cash" AND oc.old_value IS NULL AND ocs.id_admin IS NULL AND ocs.id_user IS NULL AND oc.new_value = 1', [ $order->id_order ]);
			if($oc->id_order_change_set){
				$order->campus_cash = 0;
				$order->save();
				echo $oc->id_order;
				echo "\n";
			}
		}
	}
}
