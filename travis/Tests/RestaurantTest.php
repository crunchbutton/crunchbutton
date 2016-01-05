<?php

class RestaurantTest extends PHPUnit_Framework_TestCase {

	public function testOpen() {
		$name = get_called_class();

		$r = new Restaurant([
			'name' => $name,
			'active' => 1,
			'delivery' => 1,
			'credit' => 1,
			'delivery_fee' => '1.5',
			'confirmation' => 0,
			'community' => 'test',
			'timezone' => 'UTC',
			'open_for_business' => true
		]);
		$r->save();
$date = new DateTime;
$date->modify('-5 minutes');
$end = new DateTime;
$end->modify('+5 minutes');
		$h = new Hour([
			'id_restaurant' => $r->id_restaurant,
			'day' => strtolower(date('D')),
			'time_open' => $date->format('H:i'),
			'time_close' => $end->format('H:i'), //current time
		]);
		$h->save();
		$this->assertTrue($r->open() ? true : false);
	}
}
