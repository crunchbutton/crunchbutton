<?php

$q = '
	select r.* from restaurant r
	left join restaurant_payment_type t using(id_restaurant)
	where t.verified=false
';

$restaurants = Restaurant::q($q);

foreach ($restaurants as $restaurant) {
	$restaurant->payment_type()->setStripeRep();
}

exit;