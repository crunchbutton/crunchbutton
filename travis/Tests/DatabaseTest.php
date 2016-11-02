<?php

class DatabaseTest extends PHPUnit_Framework_TestCase {
	public function testDb() {
		$c = c::db()->get('select * from config limit 1')->get(0);
		// TODO: travis fix it
		// $this->assertTrue($c->id_config ? true : false);
	}
}