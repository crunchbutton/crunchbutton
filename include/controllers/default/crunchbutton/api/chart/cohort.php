<?php

class Controller_api_chart_cohort extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			
			case 'post':
				
				if ( $_SESSION['admin'] ) {

					$name = $_REQUEST[ 'name' ];
					$address_has = $_REQUEST[ 'address_has' ];
					$name_has = $_REQUEST[ 'name_has' ];
					$pay_type_is = $_REQUEST[ 'pay_type_is' ];
					$delivery_type_is = $_REQUEST[ 'delivery_type_is' ];

					$data = array( 'address_has' => $address_has, 'name_has' => $name_has, 'pay_type_is' => $pay_type_is, 'delivery_type_is' => $delivery_type_is );

					$cohort = new Crunchbutton_Chart_Cohort();
					$cohort->name = $name;
					$cohort->data = $cohort->Form2Mysql( $data );
					$cohort->save();

					echo json_encode( ['success' => $cohort->id_chart_cohort ] );
				}

			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}
}