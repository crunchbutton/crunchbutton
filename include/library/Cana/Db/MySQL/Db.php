<?php

class Cana_Db_MySQL_Db extends Cana_Db_Base {
	public function connect($args = null) {
		$options = [];

		if (!$args->dsn) {
			$args->dsn = 'mysql:host='.$args->host.';dbname='.$args->db.';charset=utf8';
		}

		if ($args->persistent) {
			$options[PDO::ATTR_PERSISTENT] = true;
		}

		$db = new \PDO($args->dsn, $args->user, $args->pass, $options);
		$this->driver($db->getAttribute(\PDO::ATTR_DRIVER_NAME));
		$this->database($args->db);

		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

		return $db;
	}
}