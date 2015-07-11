<?php

class Cana_Db_PostgreSQL_Db extends Cana_Db_Base {
	public function connect($args = null) {
		if ($args->url) {
			preg_match('/^(postgres:\/\/)(.*):(.*)@(.*):([0-9]+)\/(.*)$/u', $args->url, $matches);
			$args->user = $matches[2];
			$args->pass = $matches[3];
			$args->host = $matches[4];
			$args->port = $matches[5];
			$args->db = $matches[6];
		}

		if (!$args->dsn) {
			$args->dsn = 'pgsql:host='.$args->host.';dbname='.$args->db.';user='.$args->user.';password='.$args->pass;
			if ($args->port) {
				$args->dsn .= ';port='.$args->port;
			}
		}
		
		echo "\n\nDSN: ".$args->dsn."\n\n";

		$db = new \PDO($args->dsn);
		$this->driver($db->getAttribute(\PDO::ATTR_DRIVER_NAME));
		$this->database($args->db);

		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

		return $db;
	}
	
	function getFields($table) {
		$res = $this->db()->query("SELECT column_name as Field, data_type as Type, is_nullable as Null, column_default as Default FROM information_schema.columns WHERE table_name = '".$table."'");
		return $res;
	}
	
	public function query($query, $args = [], $type = 'object') {
		// replace backticks
		$query = str_replace('`','"', $query);
		
		// replace add single quotes to interval statements
		$query = preg_replace('/(interval) ([0-9]+) ([a-z]+)/i','\\1 \'\\2 \\3\'', $query);
		
		// replace unix_timestamp
		$query = preg_replace('/unix_timestamp( )?\((.*?)\)/i','extract(epoch FROM \\2)', $query);
		
		// replace date_sub
		$query = preg_replace('/(date_sub\((.*?),(.*?))\)/i','\\2 - \\3', $query);
		
		// replace date formats
		$query = preg_replace_callback('/date_format\(( )?(.*?),( )?("(.*?)"|\'(.*?)\')( )?\)/i',function($m) {
			$find = ['/\%Y/', '/\%m/', '/\%d/', '/\%H/', '/\%i/', '/\%s/', '/\%W/'];
			$replace = ['YYYY', 'MM', 'DD', 'HH24', 'MI', 'SS', 'D'];
			$format = preg_replace($find, $replace, $m[6] ? $m[6] : $m[5]);
			return 'to_char('.$m[2].', \''.$format.'\')';
		}, $query);

		return parent::query($query, $args, $type);
	}
	
	public function exec($query) {
		$query = str_replace('`','"', $query);
		return parent::exec($query);
	}
}