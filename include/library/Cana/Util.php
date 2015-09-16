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

	function static sort_col($table, $colname) {
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

}