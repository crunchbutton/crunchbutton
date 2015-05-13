<?php

\Stripe\Stripe::setApiKey(c::config()->stripe->live->secret);
$cards = \Stripe\Customer::retrieve('cus_6EbmXi5atjphfp')->sources->all(['object' => 'card'])->data;

foreach ($cards as $c) {
	print_r($c->id);
}