<?php

class Cana_Db_PostgreSQL_Db extends Cana_Db_Base {
	public function connect($args = null) {
		if (!$args->dsn) {
			$args->dsn = 'pgsql:host='.$args->host.';dbname='.$args->db.';user='.$args->user.';password='.$args->pass;
		}

		$db = new \PDO($args->dsn);
		$this->driver($db->getAttribute(\PDO::ATTR_DRIVER_NAME));
		$this->database($args->db);

		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

		return $db;
	}
}