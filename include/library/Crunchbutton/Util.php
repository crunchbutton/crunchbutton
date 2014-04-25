<?php

class Crunchbutton_Util extends Cana_Model {
	public function frontendTemplates($export = false) {
		$files = [];
		
		$themes = c::view()->theme();
		$themes = array_reverse($themes);
		
		
		foreach ($themes as $theme) {
			if (file_exists(c::config()->dirs->view.$theme.'/frontend')) {
				$frontendDir = $theme;
				break;
			}
		}
		
		foreach (new DirectoryIterator(c::config()->dirs->view.$frontendDir.'/frontend') as $fileInfo) {
			if (!$fileInfo->isDot() && $fileInfo->getBasename() != '.DS_Store' ) {
				$files[] = $fileInfo->getBasename('.phtml');
			}
		}
		if ($export) {
			$files[] = 'legal';
		}
		return $files;
	}

	public static function stringToColorCode( $str ) {
		$code = dechex( crc32( $str ) );
		$code = substr( $code, 0, 6 );
  	return $code;
	}

	public static function intervalToSeconds( $interval ){
		return ( $interval->s )
         + ( $interval->i * 60 )
         + ( $interval->h * 60 * 60 )
         + ( $interval->d * 60 * 60 * 24 )
         + ( $interval->m * 60 * 60 * 24 * 30 )
         + ( $interval->y * 60 * 60 * 24 * 365 );
	}

	public static function inverseColor( $color ){
		$r = dechex( 255 - hexdec( substr( $color, 0, 2 ) ) );
		$r = ( strlen( $r ) > 1 ) ? $r : '0' . $r;
		$g = dechex( 255 - hexdec( substr( $color, 2, 2 ) ) );
		$g = ( strlen( $g ) > 1 ) ? $g : '0' . $g;
		$b = dechex( 255 - hexdec( substr( $color, 4, 2 ) ) );
		$b = ( strlen( $b ) > 1 ) ? $b : '0' . $b;
		return $r . $g . $b;
	}

	public static function isDarkColor( $color ){
		$c_r = hexdec( substr( $color, 0, 2 ) );
		$c_g = hexdec( substr( $color, 2, 2 ) );
		$c_b = hexdec( substr( $color, 4, 2 ) );
		// calc brightness value from 0 to 255
		return ( ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000 < 130 );
	}

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
		while (str($title,'--') !== false) {
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
	
	public static function relativeTime($timestamp, $timezoneTO = null, $timezoneFROM = null) {
		if (!$timezoneFROM) {
//			$timezoneFROM = new DateTimeZone('utc');
			$timezoneFROM = c::config()->timezone;
		}

		if ($timezone) {
			$d = new DateTime($timestamp, $timezoneFROM);
			$d->setTimezone($timezoneTO);
			$t = new DateTime('now', $timezoneFROM);
			$t->setTimezone($timezoneTO);

		} else {
			$d = new DateTime($timestamp);
			$t = new DateTime();
		}

		/*
		$interval = $d->diff($t);
		$difference = $interval->format('%y %m %d %h %i %s');
		return $difference;
		*/
		
		$difference = $t->getTimestamp() - $d->getTimestamp();
	
		$periods = ['sec', 'min', 'hour', 'day', 'week', 'month', 'year', 'decade'];
		$lengths = ['60','60','24','7','4.35','12','10'];

		if ($difference > 0) { // this was in the past
			$ending = 'ago';
		} else { // this was in the future
			$difference = -$difference;
			$ending = 'to go';
		}		
		for($j = 0; $difference >= $lengths[$j]; $j++) {
			$difference /= $lengths[$j];
		}
		$difference = round($difference);
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

	public static function format_price($price) {
		// $price is a number, dollars
		// returns a string prefixed with '$' suitable for display
		return money_format('$%i', $price);
	}

}