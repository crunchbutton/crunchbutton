<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {

echo '<pre>';var_dump( mysql_real_escape_string( '111' ) );exit();

// $user_auth = User_Auth::validateResetCode( '14qobk' );
echo '<pre>';var_dump( $user_auth );exit();
// die('hard');
		// $card = Crunchbutton_Pexcard_Card::change_status( 133214, 'OPEN' );
/*
$cards = ['7'=>'Brandon Hull',
'297'=>'Everett Klodt',
'284'=>'Angel Gonzalez',
'163'=>'AJ Zekanoski',
'164'=>'Adam Fain',
'178'=>'Brendan Cavanaugh',
'13'=>'Jevan Vu',
'268'=>'Arielle Jones',
'165'=>'Michael Johnson',
'157'=>'Dawood Singleton',
'162'=>'Alexander Del Toro',
'146'=>'Emilio Macias',
'159'=>'Emma Adams',
'142'=>'John Mack',
'145'=>'Katie Aguilar',
'170'=>'David Raccasi',
'167'=>'Joseph Finnerty Dahl',
'148'=>'Brisa Pedroza',
'143'=>'Michael Fergus',
'104'=>'Kimberly Gonzalez',
'122'=>'Brandon Guthrie',
'156'=>'Daniella Silva',
'126'=>'Amy Huynh',
'234'=>'Catherine Lalouh',
'135'=>'Jayson Astor',
'212'=>'Mark Phillips',
'210'=>'Diop Condelee',
'151'=>'Samantha Spaccasi',
'174'=>'Carlos Selva',
'131'=>'Andre Montgomery',
'237'=>'Xavier Macias',
'282'=>'Thomas Miller',
'113'=>'Rondell Burnham',
'281'=>'Ray Mitchell',
'166'=>'Destinee Cone',
'238'=>'Kevin Chau',
'227'=>'Trevor Lauffer',
'175'=>'Paige Butler',
'116'=>'Daniel Ayers',
'127'=>'Bryan Hancock',
'138'=>'Jason Baker',
'283'=>'Eleanor Christenson',
'276'=>'Douglas Garcia',
'1'=>'Mike McCarthy',
'153'=>'Garrest Murgatroyd'];

$card_list = Crunchbutton_Pexcard_Card::card_list();
$_errors = [];
foreach( $cards as $key => $val ){
	$_card = null;
	foreach( $card_list->body as $card ){
		if( intval( $card->lastName ) == intval( $key ) ){
			$_card = $card;
			continue;
		}
	}
	if( $_card ){
		$admin = Admin::q( 'SELECT * FROM admin WHERE name = "' . $val . '"' );
		if( $admin->id_admin ){
			foreach( $_card->cards as $__card ){
				$last_four = str_replace( 'X', '', $__card->cardNumber );
				$id_pexcard = $_card->id;
				$admin_pexcard = Cockpit_Admin_Pexcard::getByPexcard( $id_pexcard );
				$admin_pexcard->id_admin = $admin->id_admin;
				$admin_pexcard->card_serial = $_card->lastName;
				$admin_pexcard->last_four = $last_four;
				$admin_pexcard->save();
			}
		} else {
			$_errors[] = [ $key => $val ];
		}
	} else {
		$_errors[] = [ $key => $val ];
	}
}
echo json_encode( $_errors );exit;
*/
		Crunchbutton_Pexcard_Transaction::processExpenses( '11/24/2014', '11/24/2014' );

die('hard');
		echo '<pre>';var_dump(  Crunchbutton_Pexcard_Card::change_status( 133214, 'OPEN' )  );exit();
		// echo '<pre>';var_dump( $card->ping() );exit();
		// echo '<pre>';var_dump( $card->card_list() );exit();
		$cards = Crunchbutton_Pexcard_Card::details( 100255 );
		if( $cards->body && $cards->body->cards ){
			foreach( $cards->body->cards as $card ){
				// echo '<pre>';var_dump( $card );exit();
				echo '<pre>';var_dump( Crunchbutton_Pexcard_Card::change_status( $card->id, Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN ) );exit();
			}
		}

		echo '<pre>';var_dump(
			Crunchbutton_Pexcard_Card::change_status( 100254, Crunchbutton_Pexcard_Card::CARD_STATUS_BLOCKED )
			// Crunchbutton_Pexcard_Card::details( 100256 )
		 );exit();
		// echo '<pre>';var_dump( $card->create( [ 'firstName' => 'Test', 'lastName' => '001' ] ) );exit();

		die('hard');
		$set = new Crunchbutton_Settlement;
		$set->checkPaymentStatus();
		die('hard');


		$set = new Crunchbutton_Settlement;
		$set->payDriver( 622 );
		die('hard');


	$payment = Crunchbutton_Payment::o( 2167 );
	if( $payment->id_payment ){
		// $schedule = $payment->schedule_error();
		// echo '<pre>';var_dump( $schedule );exit();
		// die('hard');
	$status = $payment->checkBalancedStatus();
	echo '<pre>';var_dump( $status );exit();
		// echo json_encode( [ 'success' => $status ] );
	}




		die('hard');

		// $credit = new Crunchbutton_Balanced_Credit;
		// $credit = Balanced\Credit::get('CR7jjKTLQUMlJDP2gZs3DIcT');
		// echo '<pre>';var_dump( $credit );exit();
		Balanced\Settings::$api_key = "***REMOVED***";
		// Balanced\Settings::$api_key = "ak-test-78Lbl2ZDE9ll0zEkHMQBsDZBhCSOjdJV";

		$credit = Balanced\Credit::get('/credits/CR7jjKTLQUMlJDP2gZs3DIcT');

		// $credit = Balanced\Credit::get('/credits/CR482VqDxCRiSwUZyrpVISbK');
		echo '<pre>';var_dump( $credit->status, $credit->failure_reason );exit();
// echo '<pre>';var_dump( $credit );exit();
		$marketplace = Balanced\Marketplace::mine();
		$credits = $marketplace->credits->query()->all();

		echo '<pre>';var_dump( $credits );exit();
	}
}