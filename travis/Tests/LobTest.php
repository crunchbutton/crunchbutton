<?php

class LobTest extends PHPUnit_Framework_TestCase {

	public function testSetup() {
		try {
			$lob = new \Lob\Lob(c::config()->lob->dev->key);
			$success = true;
		} catch (Exception $e) {

		}
		$this->assertTrue($success);
	}

	public function testCheck() {
		try {
			$lob = new \Lob\Lob(c::config()->lob->dev->key);
			$c = $lob->checks()->create([
				'to' => [
					'name' => 'name',
					'address_line1' => '123 main',
					'address_city' => '123 marina del rey',
					'address_state' => 'ca',
					'address_zip' => '90292',
					'address_country' => 'US',
				],
				'from' => '_KEY_',
				'bank_account' => '_KEY_',
				'amount' => '1.00',
				'memo' => 'note',
				'message' => 'message'
			]);

			$success = $c['id'] ? true : false;
		} catch( Exception $e ) {
			$success = $e->getMessage();
		}
		$this->assertTrue($success);

	}
}
