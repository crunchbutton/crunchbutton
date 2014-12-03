<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {
die('hard');

$cards = [
'Bao Truong' => [ 'status' => 'active', 'date' => '22/10/2014', 'serial' => '4' ],
'Brandon Shundoff' => [ 'status' => 'active', 'date' => '23/10/2014', 'serial' => '8' ],
'Monte J. Ely' => [ 'status' => 'active', 'date' => '25/10/2014', 'serial' => '5' ],
'Parsa Parirokh' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '9' ],
'Devin Conatser' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '10' ],
'Chris Tolbert' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '21' ],
'Francisco Vasquez' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '14' ],
'Simo Aichouri' => [ 'status' => 'active', 'date' => '30/10/2014', 'serial' => '28' ],
'Ryan Nunley' => [ 'status' => 'active', 'date' => '30/10/2014', 'serial' => '12' ],
'Tom Fekete' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '195' ],
'Aaron Kim' => [ 'status' => 'active', 'date' => '02/11/2014', 'serial' => '25' ],
'Sarah Goldstein' => [ 'status' => 'active', 'date' => '01/11/2014', 'serial' => '56' ],
'Nick Klimek' => [ 'status' => 'active', 'date' => '01/11/2014', 'serial' => '79' ],
'Jane Vezina' => [ 'status' => 'active', 'date' => '02/11/2014', 'serial' => '88' ],
'Jacob Lubben' => [ 'status' => 'active', 'date' => '02/11/2014', 'serial' => '51' ],
'Greer Bohanon' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '68' ],
'Matthew Trnka' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '90' ],
'Luke Schmiegel' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '99' ],
'Colton Reed' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '66' ],
'Zaakirah R. Kaazim' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '27' ],
'Alec Root' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '61' ],
'Emory Johnson' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '84' ],
'Adam Bezemek' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '16' ],
'Natalie Santa' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '76' ],
'Abram Schroeder' => [ 'status' => 'active', 'date' => '05/11/2014', 'serial' => '67' ],
'Precious Jones' => [ 'status' => 'active', 'date' => '05/11/2014', 'serial' => '47' ],
'Casey Domek' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '85' ],
'Robert Warren' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '78' ],
'Sara Lind' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '86' ],
'Josh Peterson' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '91' ],
'James Gwinn' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '73' ],
'Deshawn Alan' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '74' ],
'Jesse Little' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '24' ],
'Kahealani Alexander' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '22' ],
'Perry Thomas' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '94' ],
'Steven Frasica' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '58' ],
'Jamie Jackson' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '29' ],
'Zach Sattinger' => [ 'status' => 'active', 'date' => '08/11/2014', 'serial' => '70' ],
'SunSun Gan' => [ 'status' => 'active', 'date' => '08/10/2014', 'serial' => '63' ],
'Joe Weber' => [ 'status' => 'active', 'date' => '09/11/2014', 'serial' => '71' ],
'Eric Paulsen' => [ 'status' => 'active', 'date' => '09/11/2014', 'serial' => '64' ],
'India Kinniebrew' => [ 'status' => 'active', 'date' => '09/11/2014', 'serial' => '300' ],
'Jason Miller' => [ 'status' => 'active', 'date' => '10/11/2014', 'serial' => '299' ],
'Brian Dice' => [ 'status' => 'active', 'date' => '10/11/2014', 'serial' => '296' ],
'Isaac Sanchez' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '272' ],
'Joseph Buffo' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '32' ],
'Alicia Bruce' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '83' ],
'Albert Astorga' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '290' ],
'Chris Gathof' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '95' ],
'Keron Monk' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '269' ],
'Donald Fidalgo' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '274' ],
'Jason Van Buren' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '295' ],
'Jose Zepeda' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '294' ],
'Jason Benjoya' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '279' ],
'Brandon Hull' => [ 'status' => 'active', 'date' => '13/11/2014', 'serial' => '7' ],
'Everett Klodt' => [ 'status' => 'active', 'date' => '13/11/2014', 'serial' => '297' ],
'Angel Gonzalez' => [ 'status' => 'active', 'date' => '13/11/2014', 'serial' => '284' ],
'AJ Zekanoski' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '163' ],
'Adam Fain' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '164' ],
'Brendan Cavanaugh' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '178' ],
'Jevan Vu' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '13' ],
'Arielle Jones' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '268' ],
'Michael Johnson' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '165' ],
'Dawood Singleton' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '157' ],
'Alexander Del Toro' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '162' ],
'Emilio Macias' => [ 'status' => 'active', 'date' => '16/11/2014', 'serial' => '146' ],
'Emma Adams' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '159' ],
'John Mack' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '142' ],
'Katie Aguilar' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '145' ],
'David Raccasi' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '170' ],
'Joseph Finnerty Dahl' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '167' ],
'Brisa Pedroza' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '148' ],
'Michael Fergus' => [ 'status' => 'active', 'date' => '18/11/2014', 'serial' => '143' ],
'Brandon Guthrie' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '122' ],
'Daniella Silva' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '156' ],
'Amy Huynh' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '126' ],
'Catherine Lalouh' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '234' ],
'Jayson Astor' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '135' ],
'Mark Phillips' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '212' ],
'Diop Condelee' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '210' ],
'Samantha Spaccasi' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '151' ],
'Carlos Selva' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '174' ],
'Andre Montgomery' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '131' ],
'Xavier Macias' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '237' ],
'Thomas Miller' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '282' ],
'Rondell Burnham' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '113' ],
'Ray Mitchell' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '281' ],
'Destinee Cone' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '166' ],
'Kevin Chau' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '238' ],
'Trevor Lauffer' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '227' ],
'Paige Butler' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '175' ],
'Daniel Ayers' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '116' ],
'Bryan Hancock' => [ 'status' => 'active', 'date' => '22/11/2014', 'serial' => '127' ],
'Jason Baker' => [ 'status' => 'active', 'date' => '22/11/2014', 'serial' => '138' ],
'Eleanor Christenson' => [ 'status' => 'active', 'date' => '22/11/2014', 'serial' => '283' ],
'Douglas Garcia' => [ 'status' => 'active', 'date' => '23/11/2014', 'serial' => '276' ],
'Mike McCarthy' => [ 'status' => 'active', 'date' => '23/11/2014', 'serial' => '1' ],
'Garrett Murgatroyd' => [ 'status' => 'active', 'date' => '25/11/2014', 'serial' => '153' ],
'Alex Yang' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '129' ],
'Ian Bobbitt' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '136' ],
'Janjay Knowlden' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '53' ],
'Daniel Pereira Camargo' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '42' ] ];

$customers = Crunchbutton_Pexcard_Card::card_list();

foreach( $cards as $name => $card ){

	$saved = false;

	if( $card[ 'status' ] == 'active' && is_numeric( $card[ 'serial' ] ) && strlen( $card[ 'date' ] ) == 10 ){

		$date = explode( '/', $card[ 'date' ] );
		$date = $date[ '2' ] . '-' . $date[ '1' ] . '-' . $date[ '0' ] . ' 00:00:01';
echo '<pre>';var_dump( $date );exit();
		$admin = Admin::q( 'SELECT * FROM admin WHERE name LIKE "%' . $name . '%" ' );
		if( $admin->id_admin ){
			$pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE card_serial = ' . intval( $card[ 'serial' ] ) );
			if( $pexcard->id_admin_pexcard ){
				if( $pexcard->id_admin != $admin->id_admin ){
					die( $name );
				}
			}
			foreach( $customers->body as $customer ){
				if( intval( $customer->lastName ) == intval( $card[ 'serial' ] ) ){
					$admin_pexcard = Cockpit_Admin_Pexcard::getByPexcard( $customer->id );
					$opened = false;
					if( $customer->cards && $customer->cards[ 0 ] ){
						foreach( $customer->cards as $_card ){

							if( $_card->status != Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN ){
								Crunchbutton_Pexcard_Card::change_status( $card->id, Crunchbutton_Pexcard_Card::CARD_STATUS_OPEN );
								$opened = true;
							} else {
								$opened = true;
							}
							if( $opened ){

								$last_four = str_replace( 'X', '', $_card->cardNumber );
								$admin_pexcard->card_serial = $customer->lastName;
								$admin_pexcard->last_four = $last_four;
								$admin_pexcard->id_admin = $admin->id_admin;
								$admin_pexcard->save();
								$admin_pexcard = Cockpit_Admin_Pexcard::o( $admin_pexcard->id_admin_pexcard );

								$admin = $admin_pexcard->admin();
								$payment_type = $admin->payment_type();
								$payment_type->using_pex = 1;
								$payment_type->using_pex_date = $date;
								$payment_type->save();

								$saved = true;

							}
						}
					}
				}
			}
		}
	}
	if( !$saved  ){
		echo $name . "\n";
	}
}
exit();
// echo '<pre>';var_dump( $cards );exit();

return;
$admin = Admin::o( 5 );
		$pexcard = $admin->pexcard();
		// echo '<pre>';var_dump( $pexcard );exit();
echo '<pre>';var_dump( $pexcard->removeFundsOrderCancelled( 39349 ) );exit();
		// echo '<pre>';var_dump( Cockpit_Driver_Notify::send( 5, Cockpit_Driver_Notify::TYPE_ACCESS_INFO ) );exit();;
$order = Order::o( 1222 );
echo '<pre>';var_dump( $order->user()->firstName() );exit();
		$user_auth = User_Auth::checkEmailExists( '_EMAIL' );
$message = ( $user_auth->user()->firstName() ) ? $user_auth->user()->firstName() . ', ' : Crunchbutton_Message_Sms::greeting();
echo '<pre>';var_dump( $message );exit();
		// Crunchbutton_Community_Shift::addRemoveShiftFunds();
		// echo '<pre>';var_dump( Crunchbutton_Admin_Shift_Assign::isFirstWeek( 4 ) );exit();;


		// $action = Crunchbutton_Pexcard_Action::o( 1 );
		// echo $action->json();exit;

		// Crunchbutton_Community_Shift::addFundsBeforeShiftStarts();
		// exit();

		// $admin = Admin::o( 5 );
		// $pexcard = $admin->pexcard();
		// echo '<pre>';var_dump( $pexcard->removeFundsOrderCancelled( 39126 ) );exit();
		// echo '<pre>';var_dump( $pexcard->addFundsOrderAccepeted( 39126 ) );exit();
		// echo '<pre>';var_dump( $pexcard->removeFundsShiftFinished( 1140 ) );exit();
		// echo '<pre>';var_dump( $pexcard->addShiftStartFunds( 1140 ) );exit();
		// echo '<pre>';var_dump( $pexcard->addFunds( [ 'amount' => 0.01 ] ) );exit();

	}
}