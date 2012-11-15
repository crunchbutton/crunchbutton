<?php

class Controller_admin_users extends Crunchbutton_Controller_Account {
	public function init() {

		$users = [];
		$ordered = [];
		$orders = Order::q('select * from `order` where env="live" order by date asc');
		foreach ($orders as $order) {
			if (!$users[$order->phone]) {
				$users[$order->phone]['name'] = $order->name;
			}

			$users[$order->phone]['restaurants'][] = Restaurant::o($order->id_restaurant)->name;
			if (!$users[$order->phone]['start']) {
				$users[$order->phone]['start'] = $order->date;
			}
			$users[$order->phone]['end'] = $order->date;
			$users[$order->phone]['value'] += $order->final_price;
			$users[$order->phone]['orders']++;
		}
		
		foreach ($users as $key => $user) {
			$users[$key]['restaurants'] = array_unique($user['restaurants']);
			$start = new DateTime($user['start']);
			$end = new DateTime($user['end']);
			$users[$key]['days'] = $start->diff($end)->format('%a');
			$users[$key]['valueperday'] = $users[$key]['value'] / ($users[$key]['days'] ? $users[$key]['days'] : 1);

			if (!$users[$key]['days']) {
				//$users[$key] = null;
				$nonreturnTotalValue+= $users[$key]['valueperday'];
			} else {
				$returnUsers++;
				$totalValuePerDay+= $users[$key]['valueperday']/$users[$key]['days'];
				$totalValue+= $users[$key]['value'];
				$totalRestaurantsPerCustomer+= count($users[$key]['restaurants']);
			}
			$ordered[$users[$key]['orders']]++;
		}

		$orderCount = Order::q('select * from `order` where env="live"')->count();
		
		echo 'Orders: '.$orderCount.'<br>';		
		echo 'Users: '.count($users).'<br>';
		echo 'Return Users: '.$returnUsers.'<br>';
		echo 'AVG $ per user: '.(($nonreturnTotalValue+$totalValue)/count($users)).'<br>';
		echo 'AVG $ per non return user: '.($nonreturnTotalValue/(count($users)-$returnUsers)).'<br>';
		echo 'AVG $ per return user: '.($totalValue/$returnUsers).'<br>';
		echo 'AVG $ per return day: '.($totalValuePerDay/$returnUsers).'<br>';
		echo 'Estimated 2 year user value: '.(($totalValuePerDay/$returnUsers)*365*2).'<br>';
		echo 'Estimated 2 year user value after restaurant payout: '.((($totalValuePerDay/$returnUsers)*365*2*.1)).'<br>';
		echo 'AVG restaurants per return user: '.($totalRestaurantsPerCustomer/$returnUsers).'<br>';
		
		echo '<br>Order amounts<br>';
		
		arsort($ordered);
		foreach ($ordered as $key => $order) {
			echo $key.': '.(($order/count($users))*100).'%<br>';
		}
		
		exit;
		echo '<pre>';
		print_r($users);

	}
}