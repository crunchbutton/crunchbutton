<?php
class Crunchbutton_Message extends Cana_Model {
	public static function formatNumber($num) {
		$num = preg_replace('/[^0-9]/','',$num);
		if ($num{0} === '1' && strlen($num) == 11) {
			$num = substr($num, 1);
		}
		if (strlen($num) != 10) {
			return false;
		}

		return '+1'.$num;
	}
}