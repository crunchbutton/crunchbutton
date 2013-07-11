<?php

class Crunchbutton_Chart_Cohort extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('chart_cohort')
			->idVar('id_chart_cohort')
			->load($id);
	}

	public static function get( $id, $type ){

		switch ( $type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::o( $id );
				$cohort->type = $type;
				break;

			case 'giftcard':
			case 'months':
				$cohort = new Crunchbutton_Chart_Cohort();
				$cohort->id = $id;
				$cohort->type = $type;
				$cohort->element = 'o.phone';
				break;
		}

		return $cohort;
	}

	public function toString(){
		return $this->data;
	}

	public function Form2Mysql( $array ){
		return json_encode( $array );
	}

	public static function getAll(){
	
		$data = [];
		$data[ 'cohorts' ] = [];
		$data[ 'giftcards' ] = [];
		$data[ 'months' ] = [];
		
		$cohorts = Crunchbutton_Chart_Cohort::q( 'SELECT * FROM chart_cohort ORDER BY name DESC' );
		foreach ( $cohorts as $cohort ) {
			$data[ 'cohorts' ][] = (object) array( 'name' => $cohort->name, 'id_chart_cohort' => $cohort->id_chart_cohort, 'cohort_type' => 'cohort' );
		}

		$giftcards = Crunchbutton_Promo_Group::q( 'SELECT * FROM promo_group ORDER BY name DESC' );
		foreach ( $giftcards as $giftcard ) {
			$data[ 'giftcards' ][] = (object) array( 'name' => $giftcard->name, 'id_chart_cohort' => $giftcard->id_promo_group, 'cohort_type' => 'giftcard' );
		}

		$chart = new Crunchbutton_Chart();
		$months = $chart->allMonths();
		foreach ( $months as $month ) {
			$data[ 'months' ][]  = (object) array( 'name' => $chart->parseMonth( $month, true ), 'id_chart_cohort' => $month, 'cohort_type' => 'month' );
		}

		return $data;

	}

	public function toQuery(){

		$query = '';
		switch ( $this->type ) {
			case 'cohort':
				$data = json_decode( $this->data );
				if( count( $data ) > 0 ){
					foreach ( $data as $key => $value ) {
						switch ( $key ) {
							case 'address_has':
								if( $value != '' ){
									$query .= " AND o.address LIKE '%{$value}%' ";	
								}
								break;
							case 'name_has':
								if( $value != '' ){
									$query .= " AND o.name LIKE '%{$value}%' ";	
								}
								break;
							case 'delivery_type_is':
								if( $value != '' ){
									$query .= " AND o.delivery_type = '{$value}' ";	
								}
								break;
							case 'pay_type_is':
								if( $value != '' ){
									$query .= " AND o.pay_type = '{$value}' ";	
								}
								break;
						}
					}
				}	
				break;

			case 'giftcard':
				// query here
				break;

			case 'months':
				// Do nothing the query is building at the method
				break;
		}
		return 	$query;
	}

}

