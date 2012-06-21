<?php

/**
 * SQLlite database connectivity
 *
 * @date		2012.04.03
 * @author		Devin Smith <devin@cana.la>
 *
 */

class Cana_Db_SQLite3_Db extends SQLite3 {
	use Cana_Db_Base;

	public function __construct($params) {
		parent::__construct($params->file, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
	}

	public function query($query) {
		$ret = parent::query($query);
		if ($ret === true || $ret === false) {
			return $ret;
		} else {
			if ($er = $this->lastErrorCode()) {
				$errno = $this->lastErrorMsg();
					throw new Cana_Exception_Query(['message' => $er, 'query' => $query]);
				}
			}

			$result = new Cana_Db_SQLite3_Result($ret, $this);

		return $result;
	}

	public function escape($var) {
		return $this->escapeString($var);
	}
	
	public function insertId() {
		return $this->lastInsertRowID();
	}
} 