<?php

class Cana_Db_MySQL_Db extends Cana_Db_Base {
	public function connect($args = null) {
		$options = [];

		if ($args->url) {
			preg_match('/^(mysql:\/\/)(.*):(.*)@([a-z0-9_\-\.]+)(:([0-9]+))?\/([a-z0-9\._]+)(\?sslca=(.*))?$/ui', $args->url, $matches);
			$args->user = $matches[2];
			$args->pass = $matches[3];
			$args->host = $matches[4];
			$args->port = $matches[6];
			$args->db = $matches[7];
			$args->sslca = $matches[9];
		}

		if (!$args->port) {
			$args->port = '3306';
		}

		if (!$args->dsn) {
			$args->dsn = 'mysql:host='.$args->host.';dbname='.$args->db.';charset=utf8;port='.$args->port;
		}

		if ($args->persistent) {
			$options[PDO::ATTR_PERSISTENT] = true;
		}

		if ($args->sslca) {
			$options[PDO::MYSQL_ATTR_SSL_CA] = $args->sslca;
			$options[PDO::ATTR_TIMEOUT] = 4;
			$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		}

		if (getenv('DEBUG')) {
			error_log('>> CONNECTING TO DATABASE...');
		}

		$db = new \PDO($args->dsn, $args->user, $args->pass, $options);
		$this->driver($db->getAttribute(\PDO::ATTR_DRIVER_NAME));
		$this->database($args->db);

		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

		if (getenv('DEBUG')) {
			error_log('>> CONNECTED!!');
		}

		return $db;
	}

	public function getFields($table) {
		$res = $this->db()->query('SHOW COLUMNS FROM `'.$table.'`');
		$columns = [];

		while ($row = $res->fetch()) {
			if ($row->Extra == 'auto_increment') {
				$row->auto = true;
			} else {
				$row->auto = false;
			}
			unset($row->Extra);
			$columns[] = $row;
		}
		//$res->closeCursor();

		return $columns;
	}

	public function query($query, $args = [], $type = 'object') {
		// replace bool_and
		$query = preg_replace('/(bool_and\((.*?))\)/i','max(\\2)', $query);


		if ($args) {
			foreach ($args as $k => $v) {
				if ($v === true) {
					$args[$k] = '1';
				} elseif ($v === false) {
					$args[$k] = '0';
				}
			}
		}

		return parent::query($query, $args, $type);
	}
}
