<?php

/**
 * Database connectivity base
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.09.18
 * 
 */

trait Cana_Db_Base {
	private $_host;
	private $_user;
	private $_pass;
	private $_db;
	private $_queries;
	private $_conn;
	private $_storedFields;
	private $_prefix;

	
	public function reconnect() {
		$this->connect();
		if (isset($this->_db)) {
			$this->selectDb($this->_db);
		}
	}
	
	public function get($query, $class = null) {

		$items = [];
		$res = $this->query($query);

		if ($res->numRows() < 1) {

			if ($class) {
				$items[] = Cana::factory($class,$row);
			} else {
				$items[] = $row;
			}

		} else {
			while ($row = $res->fetch($class)) {
				if ($class) {
					$items[] = Cana::factory($class,$row);
				} else {
					$items[] = $row;
				}
			}
		}
		return new Cana_Iterator($items);
	}

	public function fields($table, $fields = null) {
		if (is_null($fields) && isset($this->_storedFields[$table])) {
			return $this->_storedFields[$table];

		} elseif (!is_null($fields)) {
			$this->_storedFields[$table] = $fields;
			return $this->_storedFields[$table];
			
		} else {
			return false;
		}
	}
	
	public function queries($query = null, $result = null) {
		if (is_null($query)) {
			return $this->_queries;
		} elseif ($query && !$result) {
			$this->_queries[md5(json_encode($query))]['query'] = $query;
		} elseif($query && $result) {
			$this->_queries[md5(json_encode($query))]['result'] = $result;
		}
		return $this;
	}
	
	public function cached($query) {
		return $this->_queries[md5(json_encode($query))] ? $this->_queries[md5(json_encode($query))]['result'] : false;
	}
	
	public function __call($method, $args) {

		//if (property_exists($this, '_'.$method)) {
			if (count($args)) {
				$this->{'_'.$method} = $args[0];
				return $this;
			} else {
				return $this->{'_'.$method};
			}
		//}
	}
} 