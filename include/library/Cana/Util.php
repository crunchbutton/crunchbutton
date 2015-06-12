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
		$v = @file_get_contents(Cana::config()->dirs->root.'.git/ORIG_HEAD');
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

	public static function subtract_minutes( $time, $minutes ) {
		$h = floor( $time / 100 );
		$m = $time - ( 100 * $h );
		if( $m == 0 ){
			$m = 59;
			$h--;
		} else {
			$m--;
		}
		return intval( $h . str_pad( $m, 2, '0', STR_PAD_LEFT ) );
	}

	public static function formatMinutes( $minutes ){
		$hours = floor( $minutes / 60 );
		if( $hours > 0 ){
			$formated = $hours . ( ( $hours > 1 )	? ' hours' : ' hour' );
		} else {
			$formated = $minutes . ( ( $minutes > 1 )	? ' minutes' : ' minute' );
		}
		return [ 'minutes' => $minutes, 'formatted' => $formated ];
	}

	public static function sum_minutes( $time, $minutes ){
		if( intval( $time ) == -1 ){
			return 0;
		}
		$h = floor( $time / 100 );
		$m = $time - ( 100 * $h );
		if( $m == 59 ){
			$m = '00';
			$h++;
		} else {
			$m++;
		}
		return intval( $h . str_pad( $m, 2, '0', STR_PAD_LEFT ) );
	}

	public static function format_time( $time ) {
		$h = floor( $time / 100 );
		$m = $time - ( 100 * $h );
		if( $h >= 24 ){
			$h -= 24;
		}
		$mintute_formated = ':' . str_pad( $m, 2, '0', STR_PAD_LEFT );
		return $h . $mintute_formated;
	}

	function sort_col($table, $colname) {
		$tn = $ts = $temp_num = $temp_str = array();
		foreach ($table as $key => $row) {
			if(is_numeric(substr($row[$colname], 0, 1))) {
				$tn[$key] = $row[$colname];
				$temp_num[$key] = $row;
			}
			else {
				$ts[$key] = $row[$colname];
				$temp_str[$key] = $row;
			}
		}
		unset($table);
		array_multisort($tn, SORT_ASC, SORT_NUMERIC, $temp_num);
		array_multisort($ts, SORT_ASC, SORT_STRING, $temp_str);
		return array_merge($temp_num, $temp_str);
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