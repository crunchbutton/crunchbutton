<?php
	
$n = Admin_Notification::o(770);
$o = Crunchbutton_Order::o(46000);

$n->send($o);