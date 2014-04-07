<?php

class RestaurantTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$r = new Restaurant([
			'name' => 'Test Restaurant',
			'active' => 1
		]);
		$r->save();
		$this->r = $r->id_restaurant;
	}

	public function tearDown() {
		$r = Restaurant::o($this->r)->delete();
	}

	public function testLoading() {
		$r = Restaurant::o($this->r);
		$this->assertEquals('Test Restaurant',$r->name);
	}
}
