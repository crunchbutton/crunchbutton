<?php

/*
$r = Crunchbutton_Message_Push_Ios::send([
	'to' => '8d9b2a99aa4754686eb76ff3a20c007c808470a7327107e786f6cf0e1696f7ac',
	'message' => 'test',
	'count' => 1,
	'id' => 'order-1',
	'category' => 'order-new-test'
]);

exit;
*/
/*
$a = new Crunchbutton_Admin_Notification();

$a->resendNotification();
exit;
	
	
	
$o = Crunchbutton_Order::o(113225);
$o->notifyDrivers();

exit;
*/
//c::timeout(function() {

	$n = Admin_Notification::o($_REQUEST['id'] ? $_REQUEST['id'] : 5701);
	$o = Crunchbutton_Order::o(46000);

	$r = $n->send($o);

	var_dump($r);
	error_log(print_r($r, 1));
//});

die('sent');