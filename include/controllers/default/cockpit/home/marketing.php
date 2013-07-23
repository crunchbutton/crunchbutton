<?php

class Controller_home_marketing extends Crunchbutton_Controller_Account {

	public function init() {

		$data = [];	

		$groups = Crunchbutton_Promo_Group::q('SELECT * FROM promo_group pg WHERE pg.date_mkt > 0 ORDER BY pg.date_mkt ASC');
		foreach( $groups as $group ){
			$data[ $group->id_promo_group ] = [];
			$data[ $group->id_promo_group ][ 'name' ] = $group->name;
			$data[ $group->id_promo_group ][ 'community' ] = $group->community;
			$data[ $group->id_promo_group ][ 'promotion_type' ] = $group->promotion_type;
			$data[ $group->id_promo_group ][ 'man_hours' ] = $group->man_hours;
			$data[ $group->id_promo_group ][ 'date' ] = $group->date_mkt();
			$data[ $group->id_promo_group ][ 'report' ] = $group->mktReport();
		}
		c::view()->data = $data;
		c::view()->display('home/marketing');

	}
}