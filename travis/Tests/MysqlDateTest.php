<?php

class MysqlDateTest extends PHPUnit_Framework_TestCase {
	
	public function testDate() {
		// SET GLOBAL time_zone = timezone;
		$dbd = c::db()->get('select now()');
		$phpd = date('Y-m-d H:i:s');
		
		$this->assertEquals($dbd, $phpd);
	}
}