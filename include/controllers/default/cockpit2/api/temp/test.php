<?php

class Controller_api_temp_test extends Crunchbutton_Controller_RestAccount {

	public function init(){
		// Cockpit_Admin_Pexcard::pexCardRemoveCardFundsDaily();
		// $content = Crunchbutton_Pexcard_Resource::request( 'detailsaccount', [ 'id' => $AccountId ] );
		// echo $content;exit;
		// echo '<pre>';var_dump( Crunchbutton_Pexcard_Details::account() );exit();


		// 112966
		// 612
// Crunchbutton_Pexcard_Token::createToken();
		// Cockpit_Admin_Pexcard::pexCardRemoveCardFundsDaily();

		// echo json_encode(  Crunchbutton_Pexcard_Details::account()  );exit;

		// $pex = Cockpit_Admin_Pexcard::o( 167628 );
		// $pex->addArbitraryFunds( 5, 'test' );
		// $pex->runQueRemoveFunds();

// echo '<pre>';var_dump( Crunchbutton_Pexcard_Token::getToken() );exit();
		if( $_GET[ 'id_action' ] ){
			$action = Crunchbutton_Pexcard_Action::o( $_GET[ 'id_action' ] );
			$action->run();
		}

		// Crunchbutton_Cron_Job_CreatePexCardToken::run();

		// echo '<pre>';var_dump( $pex->properties() );exit();

		// $data = (object) [ 'name' => 'daniel' ];
		// Crunchbutton_Pexcard_Resource::saveCache( $data );
	}
}
