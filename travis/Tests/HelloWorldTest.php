<?php


class HelloWorldTest extends PHPUnit_Framework_TestCase {

	private $pdo;

	public function setUp() {
	   // $this->pdo = new PDO($GLOBALS['db_dsn'], $GLOBALS['db_username'], $GLOBALS['db_password']);
	   // $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	   // $this->pdo->query("CREATE TABLE hello (what VARCHAR(50) NOT NULL)");
	}

	public function tearDown() {
	   // $this->pdo->query("DROP TABLE hello");
	}

	public function testHelloWorld() {
		$r = new Restaurant(1);
		$this->assertEquals('Alpha Delta Pizza', $r->name);
	}

	public function testHello() {
		$this->assertEquals('Hello Bar', 'Hello Bar');
	}
}
