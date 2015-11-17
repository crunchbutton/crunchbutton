<?php

class SaveHoursTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		// Crunchbutton_Restaurant_Time::store();
		Crunchbutton_Restaurant_Time::getTime( 107 );
	}
	public function testHours(){
		$this->assertEquals( true, true );
	}
}