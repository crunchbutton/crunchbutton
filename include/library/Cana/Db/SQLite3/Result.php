<?php

/**
 * SQLite3 result set
 *
 * @date		2012.04.03
 * @author		Devin Smith <devin@cana.la>
 *
 */

class Cana_Db_SQLite3_Result extends Cana_Db_Result {
	public function numRows() {
		return $this->res()->numRows();
	}

	public function fetch($class = null, $params = []) {
		if ($class) {
			return new $class($this->res()->fetchArray(SQLITE3_ASSOC));
		} else {
			$i = $this->res()->fetchArray(SQLITE3_ASSOC);
			return $i ? (object)$i : false;
		}
	}
}