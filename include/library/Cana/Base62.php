<?php

/**
 * base62 Decimal encoder
 *
 * @author		Devin Smith <devin@cana.la>
 * @date		2009.12.09
 *
 * Convert a db index to a shorter one
 *
 */


class Cana_Base62 {
	private static $_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	private static $_base = '62';

	// encode the string
	public static function encode($val) {
		if ($val) {
			$str = '';
			do {
				$i = $val % self::$_base;
				$str = self::$_chars[$i] . $str;
				$val = ($val - $i) / self::$_base;
			} while($val > 0);
			return $str;
		} else {
			return '';
		}
	}
	
	// decode the string
	public static function decode($str) {
		if ($str) {
			$len = strlen($str);
			$val = 0;
			$arr = array_flip(str_split(self::$_chars));
			for($i = 0; $i < $len; ++$i) {
				$val += (isset($arr[$str[$i]]) ? $arr[$str[$i]] : 0) * pow(self::$_base, $len-$i-1);
			}
			return $val;
		} else {
			return '';
		}
	}
}