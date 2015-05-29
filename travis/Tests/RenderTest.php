<?php

class RenderTest extends PHPUnit_Framework_TestCase {
	public function testRender() {
		$file = file_get_contents('http://localhost/');
		$this->assertTrue(strpos($file, '</html>') ? true : false);
	}
}

