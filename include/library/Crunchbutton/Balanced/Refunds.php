<?php

class Crunchbutton_Balanced_Refunds extends Cana_Model {
	public static function byId($id) {
//		$refunds = Balanced\Refund::get(c::balanced()->debits->uri.'/'.$id.'/refunds');
		$refunds = Balanced\Refund::get('/refunds/'.$id);
		return $refunds;
	}
}