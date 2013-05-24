<?php

class Crunchbutton_Util extends Cana_Model {

	public static function ceil($value, $precision) {
		$pow = pow ( 10, $precision ); 
		return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow;
	}

	public static function encodeTitle($title) {

		$find = array(
			'/&amp;/',
			'/[^\w ]+/',
			'/ /',
		);
		$replace = array(
			' ',
			'',
			'-'
		);

		$title = preg_replace($find, $replace, $title);
		while (strpos($title,'--') !== false) {
			$title = str_replace('--','-',$title);
		}

		return strtolower(urlencode(trim(self::truncateByWord($title,50,''))));
	}
	
	public static function formatSize($size) {
		$sizes = array(' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
		if ($size == 0) {
			return('n/a');
		} else {
			return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]);
		}
	}

    public static function revision() {
		$file = file(Cana::config()->dirs->root.'.hg/branchheads.cache');
		$file = explode(' ',$file[0]);
		return trim($file[1]);
    }
	
	public static function relativeTime($timestamp){ 
		$difference = time() - strtotime($timestamp);
		$periods = array('sec', 'min', 'hour', 'day', 'week', 'month', 'year', 'decade');
		$lengths = array('60','60','24','7','4.35','12','10');

		if ($difference > 0) { // this was in the past
			$ending = 'ago';
		} else { // this was in the future
			$difference = -$difference;
			$ending = 'to go';
		}		
		for($j = 0; $difference >= $lengths[$j]; $j++) {
			$difference /= $lengths[$j];
		}$difference = round($difference);
		if ($difference != 1) {
			$periods[$j].= 's';
		}
		$text = $difference.' '.$periods[$j].' '.$ending;
		return $text;
	}

	public static function truncateByLines($string, $maxlines = 3) {
		$retVals = array();
		$string = explode("\n",trim($string));
		for ($x = 0; $x < $maxlines; $x++) {
			if (isset($string[$x])) {
				$retVals[] = $string[$x];
			}
		}
		return implode("\n",$retVals);
	}
	
	public static function truncateByWord($string, $length = 100, $suffix = '&hellip;') {
		if (strlen($string) > $length) {
			$string = explode(' ', $string);
			$newString = '';
			foreach ($string as $piece) {
				if (strlen($newString.' '.$piece) <= $length) {
					$newString .= ' '.$piece;
				} else {
					break;
				}
			}
			$string = trim($newString).$suffix;
		}
		return $string;
		
	}
	
	public function relativeTimeTz($ts, $tz) {
		
	}
	
	public function dateTimeRep($datetime, $timezome, $format = 'M j, g:i a') {
		$d = new DateTime($datetime_str, new DateTimeZone('utc'));
		$d->setTimezone($rep_timezone);
		return $d->format($format);
	}

	public static function format_phone($phone) {
		$phone = preg_replace('/[^\dx]/i', '', $phone);
		if(preg_match('/^(\d{3})(\d{3})(\d{4})$/', $phone, $m)) {
			return "($m[1]) $m[2]-$m[3]";
		}
		else {
			return $phone;
		}
	}
	
}