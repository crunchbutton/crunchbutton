<?php
	
$order = Order::o(29161);

$n = new Crunchbutton_Admin_Notification;

$n->sendFax($order);