<?php

$n = Admin_Notification::o(5524);
$o = Crunchbutton_Order::o(46000);

$r = $n->send($o);

var_dump($r);