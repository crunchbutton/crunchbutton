<?php

class Crunchbutton_Util extends Cana_Model {

	public static function isCockpit(){
		return ( $_REQUEST[ 'cockpit' ] ||
						( strpos( $_SERVER['HTTP_HOST'], 'cockpit' ) !== false )  ||
						( strpos( $_SERVER['HTTP_HOST'], 'dev.la' ) !== false )  ||
						( strpos( $_SERVER['HTTP_HOST'], 'dev.pit' ) !== false ) ) ? true : false;
	}

	public static function isCLI(){
		if( $_SERVER[ 'ACT_AS_NOT_IS_CLI' ] ){
			return false;
		}
		return getenv('CLI');
	}

	public static function dateToUnixTimestamp( $dateTime ){
		if ( is_a( $dateTime , 'DateTime' ) ) {
			$tz = $dateTime->getTimestamp();
			return gmdate( 'Y-m-d\TH:i:s\Z', $tz );
		}
		return false;
	}

	public function url(){
		return 'http://' . $_SERVER['HTTP_HOST'];
	}

	public function isMySQL(){
		return ( strtolower( c::db()->driver() ) == 'mysql' );
	}

	public static function removeNewLine( $string ){
		return trim(preg_replace( '/\s+/', ' ', $string ) );
	}

	// https://gist.github.com/maggiben/9457434
	public static function humanReadableNumbers( $number ){
		if( $number < 1000 ){
			return $number;
		}
		$si = [ 'K', 'M', 'G', 'T', 'P', 'H' ];
		$exp = floor( log( $number ) / log( 1000 ) );
		$result = $number / pow( 1000, $exp );
		$result = ( $result % 1 > ( 1 / pow( 1000, $exp - 1 ) ) ? floor( $result ) : floor( $result ) );
		return $result . $si[ $exp - 1 ];
	}

	public static function frontendTemplates($export = false) {
		$files = [];

		$themes = c::view()->theme();
		$themes = array_reverse($themes);


		foreach ($themes as $theme) {
			if (file_exists(c::config()->dirs->view.$theme.'/frontend')) {
				$frontendDir = $theme;
				break;
			}
		}

		$path = c::config()->dirs->view.$frontendDir.'frontend';
		$directory = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($directory);

		foreach ($iterator as $fileInfo) {
			$name = $fileInfo->getFilename();
			if ($name{0} != '.') {
				$files[] = substr(str_replace('.phtml','',str_replace($path, '',$fileInfo->getPathname())),1);
			}
		}

		if ($export) {
			$files[] = 'legal';
		}
		natcasesort($files);
		return $files;
	}

	public static function stringToColorCode( $str ) {
		die('#5430 deprecated');
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

	public static function intervalMoreThan24Hours( $interval ){
		return ( Crunchbutton_Util::intervalToSeconds( $interval ) > ( 60 * 60 * 24 ) );
	}

	public static function inverseColor( $color ){
		die('#5430 deprecated');
		$r = dechex( 255 - hexdec( substr( $color, 0, 2 ) ) );
		$r = ( strlen( $r ) > 1 ) ? $r : '0' . $r;
		$g = dechex( 255 - hexdec( substr( $color, 2, 2 ) ) );
		$g = ( strlen( $g ) > 1 ) ? $g : '0' . $g;
		$b = dechex( 255 - hexdec( substr( $color, 4, 2 ) ) );
		$b = ( strlen( $b ) > 1 ) ? $b : '0' . $b;
		return $r . $g . $b;
	}

	public static function isDarkColor( $color ){
		die('#5430 deprecated');
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

	public static function round_up ($value, $places=0) {
		if ($places < 0) { $places = 0; }
		$mult = pow(10, $places);
		return ceil($value * $mult) / $mult;
	}

	static function uploadWWW(){
		return '/upload/';
	}

	static function uploadPath(){
		$file_path = realpath( dirname( __FILE__ ) );
		$www_path = realpath( $file_path . '/../../../www/' );
		$upload_path = $www_path . '/upload';
		if ( !file_exists( $upload_path ) ) {
			@mkdir( $upload_path, 0777, true );
		}
		return realpath( $upload_path );
	}

	static public function allowedExtensionUpload( $ext ){
		$ext = strtolower( $ext );
		$allowed = array( 'gif','png' ,'jpg', 'doc', 'docx', 'pdf', 'jpeg' );
		return in_array( $ext, $allowed );
	}

	static public function slugify( $txt ){

		$table = array(
						'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
						'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
						'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
						'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
						'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
						'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
						'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
						'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-', '.' => '', '[' => '', ']' => ''
		);

		$stripped = preg_replace( array( '/\s{2,}/', '/[\t\n]/' ), ' ', $txt );
		return strtolower( strtr( $txt, $table ) );
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

	public static function format_phone($phone) {
		$phone = preg_replace('/[^\dx]/i', '', $phone);
		if(preg_match('/^(\d{3})(\d{3})(\d{4})$/', $phone, $m)) {
			return "($m[1]) $m[2]-$m[3]";
		}
		else {
			return $phone;
		}
	}

	function format_interval( $difference, $accuracy = 10 ) {
		$intervals = array( 'y' => ' year', 'm' => ' month', 'd' => ' day', 'h' => ' hour', 'i' => ' minute', 's' => ' second' );
		$i = 0;
		$result = '';
		foreach ( $intervals as $interval => $name ) {
			if ( $difference->$interval > 1) {
				$result .= $difference->$interval . $intervals[$interval] . 's ';
				$i++;
			} elseif ( $difference->$interval == 1) {
				$result .= $difference->$interval . $intervals[$interval] . ' ';
				$i++;
			}
			if ($i == $accuracy) {
				break;
			}
		}
		return $result;
	}

	public static function interval2Hours( $difference, $accuracy = 2 ) {
		$minutes = self::interval2Minutes($difference, $accuracy);
		if($minutes){
			return $minutes / 60;
		}
		return 0;
	}

	public static function interval2Minutes( $difference, $accuracy = 2 ) {
		$seconds = 	( $difference->s )
							+ ( $difference->i * 60 )
							+ ( $difference->h * 60 * 60 )
							+ ( $difference->d * 60 * 60 * 24 )
							+ ( $difference->m * 60 * 60 * 24 * 30 )
							+ ( $difference->y * 60 * 60 * 24 * 365 );
		return intval($seconds / 60);
	}

	public static function randomPass( $length = 6 ){
		$characters = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
		$pass = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$pass .= strtolower( $characters[ rand( 0, strlen( $characters ) - 1 ) ] );
		}
		return $pass;
	}


	public static function formatAddress($address = '') {
		if (!$address) {
			return false;
		}

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address);

		$res = @json_decode(@file_get_contents($url));
		if (!$res) {
			return $address;
		}
		$res = $res->results[0];

		/*
		$parts = [];
		foreach ($res->address_components as $item) {
			$parts[$item->types[0]] = $item->short_name;
		}
		*/

		$f = explode(',',$res->formatted_address);
		array_pop($f);
		$formatted = array_shift($f)."\n".trim(implode(',',$f));

		return $formatted;
	}

	public static function addressParts($address = '') {
		if (!$address) {
			return false;
		}
		$parts = explode("\n", trim($address));
		$parts[1] = explode(',', trim($parts[1]));
		$parts[1][1] = explode(' ', trim($parts[1][1]));

		return [
			'address' =>$parts[0],
			'city' => $parts[1][0],
			'state' => $parts[1][1][0],
			'zip' => $parts[1][1][1]
		];
	}

}
