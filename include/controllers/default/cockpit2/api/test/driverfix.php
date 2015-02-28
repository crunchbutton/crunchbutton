<?php

$q = '
select `order`.* from `order`
left join restaurant using (id_restaurant)
left join admin_notification_log using (id_order)
where restaurant.delivery_service=1 and restaurant.active=true and admin_notification_log.id_admin_notification_log is null
and `order`.date > date_sub(now(), interval 10 minute)
order by `order`.id_order desc
';
$orders = Order::q($q);

foreach ($orders as $order) {
	echo $order->id_order."\n";
	$order->notifyDrivers();
}

echo 'done';