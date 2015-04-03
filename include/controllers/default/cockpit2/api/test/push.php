<?php


$r = Crunchbutton_Message_Push_Ios::send([
	'to' => '8d9b2a99aa4754686eb76ff3a20c007c808470a7327107e786f6cf0e1696f7ac',
	'message' => 'test',
	'count' => 1,
	'id' => 'order-1',
	'category' => 'order-new-test',
	'env' => 'live'
]);

exit;

$n = Admin_Notification::o(833);
$o = Crunchbutton_Order::o(46000);

$r = $n->send($o);

var_dump($r);