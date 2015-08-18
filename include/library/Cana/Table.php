<?php

/**
 * Table class
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.09.22
 *
 * The table class is extended by all database table objects as
 * an easy dbo handler.
 *
 * The table class also includes several quick load methods
 *
 * ex:
 *		class myapp_myObject extends Cana_Table {
 *			...
 *		}
 *		// auto object mapping allows you to omit the myapp_ as its an alias
 *		$object = new myObject($id)
 *		// $object has all the properties of your row
 *		echo $object->var;
 *
 * ex2:
 *		$object = new myObject;
 *		// contains empty values for all columns
 *
 * ex3:
 *		$object = myObject::o($id);
 *		// returns an Iteration loaded from Factory
 *
 * ex4:
 *		foreach (myObject::o($id, $id2) as $object) {
 *			// $object is in instance of myapp_myObject
 *		}
 *
 * ex5:
 *		foreach (myObject::q('select * from objects where stuff="awesome"') as $object) {
 *			// $object is an instance of myObject loaded with everything from the db
 *		}
 */


class Cana_Table extends Cana_Model { //
	private $_table;
	private $_id_var;
	private $_fields;
	private $_db;
	private $_properties;
	private $_jsonParsing = false;

	/**
	 * Json booleans are tricky as the can return different values for true or false
	 *
	 * Cana_Table does not store boolean values, so the boolean is turned to
	 * integer to be stored.
	 *
	 * @param array  $array    Where to look for the key
	 * @param string $key      What to look in the $array
	 * @param bool   $default  What to return if not found
	 *
	 * @return int
	 */
	protected function _jsonBoolean($array, $key, $default = false)
	{
		$return = $default;
		if (isset($array[$key])){
			switch ($array[$key]) {
				case 'true':
				case '1':
					// case 1:
					// case true:
					$return = true;
					break;
				case 'false':
				case '0':
					// case 0:
					// case false:
					$return = false;
					break;
				default:
					throw new Exception("Unrecognized JSON boolean value '{$array[$key]}' for key '$key'");
			}
		}
		return (int) $return;
	}

	/**
	 * Allows to overwrite a default filter for the where param
	 *
	 * @param array $default Default values
	 * @param array $param   The params to overwrite the defaults
	 *
	 * @return string
	 *
	 * @todo Function only allos AND concatenations
	 * @todo forces all values to be strings
	 * @todo does not clear SQL injection
	 */
	protected function _mergeWhere($default, $param)
	{
		$where    = array_merge($default, $param);
		$whereSql = '1 = 1 ';
		foreach ($where as $key => $value) {
			if ($value !== NULL) {
				$whereSql .= " AND $key = '$value'";
			}
		}
		return $whereSql;
	}

	/**
	 * Retrieve a field list from the db
	 *
	 * Will populate $this->fields based on he columns in the db for the
	 * current objects table.
	 *
	 * @return array
	 */
	public function fields() {
		if ($fields = $this->db()->fields($this->table())) {
			$this->_fields = $fields;
		} else {

			$fields = [];
			$res = $this->db()->getFields($this->table());

			foreach ($res as $row) {
				$row = get_object_vars($row);
				$props = [];

				foreach ($row as $k => $v) {
					$props[strtolower($k)] = $v;
				}
				$row = (object)$props;

				$row->null = $row->null == 'YES' ? true : false;
				if ( strpos($row->type, 'enum') === false && strpos($row->type, 'int') !== false) {
					if ($row->type == 'tinyint(1)' || $row->type == 'tinyint(1) unsigned') {
						$row->type = 'boolean';
					} else {
						$row->type = 'int';
					}
				}
				$fields[] = $row;
			}

			$this->_fields = $fields;
			$this->db()->fields($this->table(), $fields);
		}
		return $this->_fields;
	}

	/*
	public function keys() {
		// not done with this
		if ($keys = $this->db()->keys($this->table())) {
			$this->_keys = $keys;
		} else {
			$keys = [];
			$res = $this->db()->query('
				select
					table_name,column_name,referenced_table_name,referenced_column_name
				from
					information_schema.key_column_usage
				where
					referenced_table_name is not null
					and table_schema = "'.$this->db()->database().'"
					and table_name = "'.$this->table().'"
			');

			while ($row = $res->fetch()) {
				$keys[] = $row;
			}

			$this->_keys = $keys;
			$this->db()->keys($this->table(), $keys);
		}

		return $this->_keys;

	}
	*/


	/**
	 * Load the object with properties
	 *
	 * Passing in an object will populate $this with the current vars of that object
	 * as public properties. Passing in an int id will load the object with the
	 * table and key associated with the object.
	 *
	 * @param $id object|int
	 */
	public function load($id = null) {
		if (is_object($id)) {
			$node = $id;
		} elseif (is_array($id)) {
			$node = (object)$id;
		} else {
			if ($id) {
				if ($this->_jsonParsing) {
					$json = @json_decode($id);
					if (is_object($json)) {
						$node = $json;
						$id = $node->{$this->idVar()};
					}
				}

				if (!$node) {
					$query = 'SELECT * FROM `' . $this->table() . '` WHERE `'.$this->idVar().'`=:id LIMIT 1';
					$node = $this->db()->get($query, ['id' => $id])->get(0);
				}

				if (!$node) {
					$node = new Cana_Model;
				}

				if (!isset($this->_noId)) {
					$node->id = $id;
				}
			} else {
				// fill the object with blank properties based on the fields of that table
				foreach ($this->fields() as $field) {
					$this->{$field->field} = $this->{$field->field} ? $this->{$field->field} : '';
				}
			}
		}

		if (isset($node)) {
			if (isset($node->id)) {
				$this->id = $node->id;
			}
			foreach(get_object_vars($node) as $var => $value) {
				$this->$var = $value;
			}
			if (!isset($this->id) && $this->idVar()) {
				$id_var = $this->idVar();
			}
			if (!isset($this->id) && isset($node->$id_var)) {
				$this->id = $node->$id_var;
			}
		}

		foreach ($this->fields() as $field) {
			switch ($field->type) {
				case 'int':
					$this->{$field->field} = (int)$this->{$field->field};
					break;
				case 'boolean':
					$this->{$field->field} = $this->{$field->field} ? true : false;
					break;
			}
		}

		if (Cana::config()->cache->object !== false) {
			Cana::factory($this);
		}

		return $this;
	}


	/**
	 * Saves an entry in the db. if there is no curerent id it will add one
	 */
	public function save($newItem = 0) {
		$query		= '';

		//If there's an ID, and it's not null, it's an update, not insert
		if ($newItem) {
			$this->{$this->idVar()} = $newItem;
		} elseif (isset($this->_properties[$this->idVar()]) && $this->{$this->idVar()}) {
			$newItem = 0;
		} else {
			$newItem = 1;
		}

		if ($newItem) {
			$query = 'INSERT INTO `'.$this->table().'`';
		} else {
			$query = 'UPDATE `'.$this->table().'`';
		}

		$fields = $this->fields();

		$numset = 0;

		foreach ($fields as $field) {
			if ($field->auto === true && $newItem) {
				continue;
			}

			if ($this->{$field->field} == '' && $field->null) {
				$this->{$field->field} = null;
			} elseif ($this->{$field->field} == null && !$field->null) {
				$this->{$field->field} = '';
			}

			switch ($field->type) {
				case 'boolean':
					$this->{$field->field} = $this->{$field->field} ? true : false;
					break;

				case 'int':
					$this->{$field->field} = intval($this->{$field->field});
					break;

				case 'datetime':
					if ($this->{$field->field} == '0000-00-00 00:00:00' && $field->null) {
						$this->{$field->field} = null;
					}
					break;

				case 'date':
					if ($this->{$field->field} == '0000-00-00' && $field->null) {
						$this->{$field->field} = null;
					}
					break;
			}

			$this->{$field->field} = (!$this->{$field->field} && $field->null) ? null : $this->{$field->field};

			$query .= !$numset ? ($newItem ? '(' : ' SET ') : ',';
			if ($newItem) {
				$query .= ' `'.$field->field.'` ';
			} else {
				$query .= ' `'.$field->field.'`=:'.$field->field;
			}

			$fs[$field->field] = $this->{$field->field};

			$numset++;

		}

		if ($newItem) {
			$query .= ' ) VALUES (';
			$numset = 0;

			foreach ($fields as $field) {
				if ($field->auto === true) {
					continue;
				}

				$query .= !$numset ? '' : ',';
				$query .= ' :'.$field->field;
				$numset++;
			}

			$query .= ')';
		} else {
			$query .= ' WHERE '.$this->idVar().'=:id';
			$fs['id'] = $this->{$this->idVar()};
		}

		$this->dbWrite()->query($query, $fs);

		if ($newItem) {
			$this->{$this->idVar()} = $this->dbWrite()->lastInsertId();
		}
		return $this;
	}


	/**
	 * Delete a row in a table
	 */
	public function delete() {
		if ($this->{$this->idVar()}) {
			$query = 'DELETE FROM `'.$this->table().'` WHERE `'.$this->idVar().'` = ?';
			$this->dbWrite()->query($query, [$this->{$this->idVar()}]);
		} else {
			throw new Exception('Cannot delete. No ID was given.<br>');
		}
		return $this;
	}

	public function strip() {
		$fieldsMeta = $this->fields();
		foreach ($fieldsMeta as $field) {
			$fields[] = $field->field;
		}

		$vars = get_object_vars($this);
		foreach ($vars as $key => $var) {
			if (!in_array($key, $fields) && $key{0} != '_') {
				unset($this->$key);
			}
		}
		return $this;
	}

	public function serialize($array) {
		foreach ($array as $key => $val) {
			if (array_key_exists($key, $this->properties())) {
				$this->$key = $val;
			}
		}
		return $this;
	}

	public function __construct($db = null) {
		if (is_null($db)) {
			$this->db(Cana::db());
		} else {
			$this->db($db);
		}
	}

	public static function fromTable($table = null, $id_var = null, $db = null) {
		$newTable = new Cana_Table($db);

		$newTable->table($table)->idVar($id_var);
		if ($newTable->table() && $newTable->idVar()) {
			$newTable->load();
		}
		return $newTable;
	}

	public function db($db = null) {
		if (!is_null($db)) {
			$this->_db = $db;
		} else if (!isset($this->_db)) {
			$this->_db = c::db();
		}
		return $this->_db;
	}

	public function dbWrite($db = null) {
		if (!is_null($db)) {
			$this->_dbWrite = $db;
		} else if (!isset($this->_dbWrite)) {
			$this->_dbWrite = c::dbWrite();
		}
		return $this->_dbWrite;
	}

	public function idVar($id_var = null) {
		if (is_null($id_var)) {
			return $this->_id_var;
		} else {
			$this->_id_var = $id_var;
			return $this;
		}
	}

	public function table($table = null) {
		if (is_null($table)) {
			return $this->_table;
		} else {
			$this->_table = $table;
			return $this;
		}
	}

	public function properties() {
		return $this->_properties;
	}

	public function property($name) {
		return isset($this->_properties[$name]) ? $this->_properties[$name] : null;
	}

	public function &__get($name) {
		if (isset($name{0}) && $name{0} == '_') {
			return $this->{$name};
		} else {
			return $this->_properties[$name];
		}
	}

	public function __set($name, $value) {
		if ($name{0} == '_') {
			return $this->{$name} = $value;
		} else {
			return $this->_properties[$name] = $value;
		}
	}

	public function __isset($name) {
		return $name{0} == '_' ? isset($this->{$name}) : isset($this->_properties[$name]);
	}

	public static function o() {
		$classname = get_called_class();
		foreach (func_get_args() as $arg) {
			if (is_array($arg)) {
				foreach ($arg as $item) {
					$items[] = Cana::factory($classname,$item);
				}
			} else {
				$items[] = Cana::factory($classname,$arg);
			}
		}

		if (count($items) == 1) {
			return array_pop($items);
		} else {
			return new Cana_Iterator($items);
		}

	}

	public function s() {
		if (func_num_args() == 2) {
			$this->{func_get_arg(0)} = func_get_arg(1);
		} elseif (func_num_args() == 1 && is_array(func_get_arg(0))) {
			foreach (func_get_arg(0) as $key => $value) {
				$this->{$key} = $value;
			}
		}
		return $this;
	}

	public static function l($list) {
		$list = Cana_Model::l2a($list);
		return self::o($list);
	}

	public static function c($list) {
		$list = Cana_Model::l2a($list, ',');
		return self::o($list);
	}

	// show query
	public static function sq($query, $args = [], $db = null) {
		$db = $db ? $db : Cana::db();
		return $db->show_query($query, $args);
	}

	public static function q($query, $args = null, $db = null) {
		$db = $db ? $db : Cana::db();
		$res = $db->query($query, $args);
		$classname = get_called_class();
		while ($row = $res->fetch()) {
			$items[] = new $classname($row);
		}
		return new Cana_Iterator($items);
	}

	public function json() {
		return json_encode($this->exports());
	}

	public function exports() {
		return $this->properties();
	}

	public function csv() {
		$csv = $this->properties();
		if ($this->idVar() != 'id') {
			unset($csv['id']);
		}
		return $csv;
	}

	public function __toString() {
		return print_r($this->properties(),1);
	}

	public function dump() {
		echo get_class()." Object (\n";
		foreach ($this->properties() as $key => $value) {
			echo '['.$key.'] => '.$value."\n";
		}
		echo ")\n";
	}

	public function get() {
		return $this;
	}

}