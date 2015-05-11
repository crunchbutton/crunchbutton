<?php

class Cana_Db_Base {
	protected $_db;
	protected $_args;
	private $_fields;

	public function __construct($args = []) {
		if (!$args) {
			throw new Exception('Invalid DB config.');
		}
		
		if (is_array($args)) {
			$args = Cana_Model::toModel($args);
		}

		$this->db($this->connect($args));
		$this->_args = $args;
	}

	public function __sleep() {
		return ['_args'];
	}
	
	public function __wakeup() {
		$this->db($this->connect($this->_args));
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
			throw new Exception('Invalid arguments for query');
		}

		/*
		if ($args && array_keys($args) !== range(0, count($args) - 1)) {
			foreach ($args as $key => $value) {
				switch (gettype($value)) {
					case 'integer':
						$type = PDO::PARAM_INT;
						break;
					case 'string':
						$type = PDO::PARAM_STR;
						break;
					default:
						$type = null;
						break;
				}
				$stmt->bindValue(':'.$key, $value, $type);
			}
			$args = null;
		}
		*/

		try {
			$stmt->execute($args);
		} catch (Exception $e) {
			throw new Exception($query."\n".print_r($args,1)."\n".$e->getMessage());
		}

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