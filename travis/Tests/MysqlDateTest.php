<?php

class MysqlDateTest extends PHPUnit_Framework_TestCase {

	public function testDate() {
		// SET GLOBAL time_zone = timezone;
		$dbd = c::db()->get('select now() as d')->get(0);
		$phpd = date('Y-m-d H:i:s');

		$r = function($i) {
			return substr($i, 0, -(strlen($i) - strrpos($i, ':')));
		};

		$this->assertEquals($r($dbd->d), $r($phpd);
	}
}
