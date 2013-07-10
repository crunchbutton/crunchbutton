<?php

class Crunchbutton_Chart_Cohort extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('chart_cohort')
			->idVar('id_chart_cohort')
			->load($id);
	}

	public static function get( $id ){
		return Crunchbutton_Chart_Cohort::o( $id );
	}

	public function toString(){
		return $this->data;
	}

	public function Form2Mysql( $array ){
		return json_encode( $array );
	}

	public static function getAll(){
		return Crunchbutton_Chart_Cohort::q( 'SELECT * FROM chart_cohort ORDER BY name DESC' );
	}

	public function toQuery(){
		
		$query = '';
		
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

		return 	$query;
	}

}

