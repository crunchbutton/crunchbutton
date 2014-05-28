<?php

/**
 * Mysql result set
 *
 * @date		2012.03.27
 * @author		Devin Smith <devin@cana.la>
 *
 */

class Cana_Db_MySQL_Result extends Cana_Db_Result {
	public function numRows() {
		return $this->db()->affected_rows;
	}

	// do not pass this the mysql result!
	public function fetch($class = null, $params = []) {
		if (!is_object($this->res())) {
			die('Unable to connect to the database');
			exit;
		}

		if ($class) {
			return $this->res()->fetch_object($class, $params);
		} else {
			return $this->res()->fetch_object();
		}

	}
}

