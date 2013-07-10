<?php

class Controller_charts extends Crunchbutton_Controller_Account {

	public function init() {

		switch ( c::getPagePiece(1) ) {
			
			case 'cohort':
				switch ( c::getPagePiece(2) ) {
					case 'new':
							$this->cohort_new();
							break;	
					case 'remove':
							$this->cohort_remove();
							break;
					default:
						$this->cohort();
						break;
				}

				break;
			
			default:
				header( 'Location: /home' );
				break;
		}
	}

	public function cohort(){
		c::view()->cohorts = Crunchbutton_Chart_Cohort::q( 'SELECT * FROM chart_cohort ORDER BY name DESC' );
		c::view()->display( 'charts/cohort/index' );
	}

	public function cohort_new(){
		c::view()->display( 'charts/cohort/form' );
	}

	public function cohort_remove(){
		$id_chart_cohort = $_POST[ 'id_chart_cohort' ];
		$cohort = Crunchbutton_Chart_Cohort::o( $id_chart_cohort );
		if( $cohort->id_chart_cohort ){
			$cohort->delete();
		}
		echo 'ok';
	}

}