<?php

/**
 * Exception
 * 
 * @author		Devin Smith <devin@cana.la>
 * @date		2006.05.01
 * 
 */

class Cana_Exception_Query extends Exception {
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		echo $message['query'];
		parent::__construct($message['message'], $code, $previous);
	}
}