<?php

/**
 * Parse a csv file or string
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2010.10.01
 *
 * @author		Ming Hong (http://minghong.blogspot.com/)
 * @date		2006.07.24
 *
 */

class Cana_Csv extends Cana_Model {
	private $_delimiter			= ',';					// Field delimiter
	private $_enclosure			= '"';					// Field enclosure character
	private $_inputEncoding		= '';					// Input character encoding
	private $_outputEncoding	= 'ISO-8859-1//TRANSLIT';			// Output character encoding
	private $_useHeaders		= false;				// use the first line headers to make an assoc array
	private $_data				= [];					// CSV data as 2D array
	private $_columns			= [];					// used if we have headers
	
	public function __construct($params = []) {
		if (isset($params['delimiter'])) {
			$this->_delimiter = $params['delimiter'];
		}
		
		if (isset($params['enclosure'])) {
			$this->_enclosure = $params['enclosure'];
		}
		
		if (isset($params['inputEncoding'])) {
			$this->_inputEncoding = $params['inputEncoding'];
		}
		
		if (isset($params['outputEncoding'])) {
			$this->_outputEncoding = $params['outputEncoding'];
		}
		
		if (isset($params['useHeaders'])) {
			$this->_useHeaders = $params['useHeaders'];
		}
	}
	
	public function parse($mixed, $hasBOM = false) {
		if (file_exists($mixed)) {
			if (is_readable($mixed)) {
				 return $this->fromString(file_get_contents($mixed), $hasBOM);
			} else {
				return false;
			}
		} else {
			return $this->fromString($mixed, $hasBOM);
		}
	}

	/**
	 * Parse CSV from string
	 * @param   content	 The CSV string
	 * @param   hasBOM	  Using BOM or not
	 * @return Success or not
	 */	
	public function fromString($content, $hasBOM = false) {
		//$content = iconv($this->_inputEncoding, $this->_outputEncoding, $content);
		$content = str_replace("\r\n", "\n", $content);
		$content = str_replace("\r", "\n", $content);
		if ($hasBOM) { 								// Remove the BOM (first 3 bytes)
			$content = substr($content, 3);
		}
		if ($content[strlen($content)-1] != "\n") {   // Make sure it always end with a newline
			$content .= "\n";
		}

		// Parse the content character by character
		$row = [''];
		$idx = 0;
		$quoted = false;
		for ($i = 0; $i < strlen($content); $i++) {
			$ch = $content[$i];
			if ($ch == $this->_enclosure) {
				$quoted = !$quoted;
			}

			// End of line
			if ($ch == "\n" && !$quoted) {
				// Remove enclosure delimiters
				for ($k = 0; $k < count($row); $k++) {
					if ($this->_useHeaders && isset($this->_data[0])) {
						$key = $this->_data[0][$k];
						$row[$key] = $row[$k];
						unset($row[$k]);
					} else {
						$key = $k;
					}
					if ($row[$key] != '' && $row[$key][0] == $this->_enclosure) {
						$row[$key] = substr( $row[$key], 1, strlen($row[$key]) - 2);
					}
					$row[$key] = str_replace( str_repeat($this->_enclosure, 2), $this->_enclosure, $row[$key]);
				}

				// Append row into table
				$this->_data[] = $row;
				$row = [''];
				$idx = 0;

			} elseif ($ch == $this->_delimiter && !$quoted) {
				$row[++$idx] = '';

			} else {
				$row[$idx] .= $ch;
			}
		}
		if ($this->_useHeaders) {
			$this->_columns = array_shift($this->_data);
		}
		return $this->_data;
	}
	
	public function data() {
		return $this->_data;
	}
	
	public function columns() {
		return $this->_columns;
	}
}