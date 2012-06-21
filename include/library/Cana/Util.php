<?php

/**
 * Some utils
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.11.11
 *
 */

class Cana_Util extends Cana_Model {
	public static function gitVersion() {
		$v = file_get_contents(Cana::config()->dirs->root.'.git/ORIG_HEAD');
		return trim($v);
	}

	
	public static function avsort(&$array, $key, $reverse = false, $function = 'strcasecmp') {
		if (is_array($array))
			foreach ($array as $item)
				break;

		if (is_array($item)) {
			$f = function($a, $b) use ($key, $function) { return $function($a[$key],$b[$key]); };

		} elseif(is_object($item)) {
			$f = function($a, $b) use ($key, $function) { return $function($a->$key,$b->$key); };
		}

		uasort($array, $f);

		return $reverse ? array_reverse($array) : $array;
	}
	

	public static function convertBytes($bytesIn,  $from = 'bytes', $to = 'bytes') {
	
		if (preg_replace('/[a-z]/i','',$bytesIn) != trim($bytesIn)) {
			$bytes = preg_replace('/[a-z]/i','',$bytesIn);
			$fromTest = preg_replace('/[0-9\.]/i','',trim($bytesIn));
			$from = $fromTest ? $fromTest : $from;
		} else {
			$bytes = $bytesIn;
		}
		
		$lower = function($i) {
			return strtolower(substr($i,0,1));
		};
		$from = $lower($from);
		$to = $lower($to);

		$pows = array_flip(['b','k','m','g','t','p']);
		$bytes = $pows[$from] ? $bytes*(pow(1024,$pows[$from])) : $bytes;		
		$bytes = $pows[$to] ? $bytes/(pow(1024,$pows[$to])) : $bytes;
		return round($bytes,2);
	}
}