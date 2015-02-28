<?php

class Cana_Db_Base {
	private $_db;
	private $_fields;

	public function __construct($args = []) {
		if (!$args) {
			throw new Exception('Invalid DB config.');
		}
		
		if (is_array($args)) {
			$args = Cana_Model::toModel($args);
		}

		$db = $this->connect($args);
		$this->db($db);
	}

	public function connect($args = null) {
		throw new Exception('Invalid DB config.');
	}
	
	public function exec($query) {
		return $this->db()->exec($query);
	}

	public function query($query, $args = []) {
		$stmt = $this->db()->prepare($query);

		// if we have keyword arguments
		if (is_object($args)) {
			//var_dump($args); exit;
			throw new Exception('blah');
		}
		if ($args && array_keys($args) !== range(0, count($args) - 1)) {
			/*
			foreach ($args as $key => $value) {
				$stmt->bindValue(':'.$key, $value);
			}
			$args = null;
			*/
		}

		$stmt->execute($args);
		//$db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql'
		return $stmt;
	}
	
	public function get($query, $args = [], $type = 'object') {
		$stmt = $this->query($query, $args);
		return new Cana_Iterator($stmt->fetchAll($type == 'object' ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC));
	}
	
	public function db($db = null) {
		if (!is_null($db)) {
			$this->_db = $db;
		}
		return $this->_db;
	}
	
	public function fields($table, $fields = null) {
		if ($table && $fields) {
			$this->_fields[$table] = $fields;
		}
		return $this->_fields[$table];
	}
	
	public function keys($table, $keys = null) {
		if ($table && $keys) {
			$this->_keys[$table] = $keys;
		}
		return $this->_keys[$table];
	}
	
	public function driver($driver = null) {
		if (!is_null($driver)) {
			$this->_driver = $driver;
		}
		return $this->_driver;
	}
	
	public function database($database = null) {
		if (!is_null($database)) {
			$this->_database = $database;
		}
		return $this->_database;
	}
	
	public function lastInsertId() {
		return $this->db()->lastInsertId();
	}
}