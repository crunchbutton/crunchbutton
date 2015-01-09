<?php

// Prep for Settlement Test #3516
class Controller_Api_Script_PexCardAdmin extends Crunchbutton_Controller_RestAccount {

	public function init() {

		// $this->processAndValidateCSVData();

		$this->insertData();

		// die('hard');


	}

	public function insertData() {

		$cards = [
// 'David Klumpp' => [ 'status' => '', 'date' => 'n/a', 'serial' => '', 'id_admin' => '3' ],
'Bao Truong' => [ 'status' => 'active', 'date' => '22/10/2014', 'serial' => '4', 'id_admin' => '512' ],
'Brandon Shundoff' => [ 'status' => 'active', 'date' => '23/10/2014', 'serial' => '8', 'id_admin' => '570' ],
'Monte J. Ely' => [ 'status' => 'active', 'date' => '25/10/2014', 'serial' => '5', 'id_admin' => '515' ],
'Parsa Parirokh' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '9', 'id_admin' => '585' ],
'Devin Conatser' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '10', 'id_admin' => '532' ],
'Chris Tolbert' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '21', 'id_admin' => '499' ],
'Francisco Vasquez' => [ 'status' => 'active', 'date' => '29/10/2014', 'serial' => '14', 'id_admin' => '518' ],
'Simo Aichouri' => [ 'status' => 'active', 'date' => '30/10/2014', 'serial' => '28', 'id_admin' => '599' ],
'Ryan Nunley' => [ 'status' => 'active', 'date' => '30/10/2014', 'serial' => '12', 'id_admin' => '569' ],
'Tom Fekete' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '195', 'id_admin' => '398' ],
'Aaron Kim' => [ 'status' => 'active', 'date' => '02/11/2014', 'serial' => '25', 'id_admin' => '594' ],
'Sarah Goldstein' => [ 'status' => 'active', 'date' => '01/11/2014', 'serial' => '56', 'id_admin' => '608' ],
// // 'Jeff Jacquay' => [ 'status' => '', 'date' => 'n/a', 'serial' => '30', 'id_admin' => '598' ],
'Nick Klimek' => [ 'status' => 'active', 'date' => '01/11/2014', 'serial' => '79', 'id_admin' => '620' ],
'Jane Vezina' => [ 'status' => 'active', 'date' => '02/11/2014', 'serial' => '88', 'id_admin' => '560' ],
'Jacob Lubben' => [ 'status' => 'active', 'date' => '02/11/2014', 'serial' => '51', 'id_admin' => '450' ],
// // 'John Barry' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '', 'id_admin' => '514' ],
'Greer Bohanon' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '68', 'id_admin' => '610' ],
'Matthew Trnka' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '90', 'id_admin' => '619' ],
'Luke Schmiegel' => [ 'status' => 'active', 'date' => '03/11/2014', 'serial' => '99', 'id_admin' => '607' ],
'Colton Reed' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '66', 'id_admin' => '632' ],
'Zsatasia Green' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '27', 'id_admin' => '597' ], // -> here
'Alec Root' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '61', 'id_admin' => '312' ],
'Emory Johnson' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '84', 'id_admin' => '622' ],
'Adam Bezemek' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '16', 'id_admin' => '578' ],
'Natalie Santa' => [ 'status' => 'active', 'date' => '04/11/2014', 'serial' => '76', 'id_admin' => '556' ],
'Abram Schroeder' => [ 'status' => 'active', 'date' => '05/11/2014', 'serial' => '67', 'id_admin' => '621' ],
'Precious Jones' => [ 'status' => 'active', 'date' => '05/11/2014', 'serial' => '47', 'id_admin' => '644' ],
'Casey Domek' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '85', 'id_admin' => '624' ],
'Robert Warren' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '78', 'id_admin' => '641' ],
'Sara Lind' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '86', 'id_admin' => '557' ],
'Josh Peterson' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '91', 'id_admin' => '703' ], // -> here
'James Gwinn' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '73', 'id_admin' => '617' ],
'Deshawn Alan' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '74', 'id_admin' => '634' ],
'Jesse Little' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '24', 'id_admin' => '659' ],
'Kahealani Alexander' => [ 'status' => 'active', 'date' => '06/11/2014', 'serial' => '22', 'id_admin' => '657' ],
'Perry Thomas' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '94', 'id_admin' => '630' ],
'Steven Frasica' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '58', 'id_admin' => '209' ],
'Jamie Jackson' => [ 'status' => 'active', 'date' => '07/11/2014', 'serial' => '29', 'id_admin' => '662' ],
'Zach Sattinger' => [ 'status' => 'active', 'date' => '08/11/2014', 'serial' => '70', 'id_admin' => '615' ],
'SunSun Gan' => [ 'status' => 'active', 'date' => '08/10/2014', 'serial' => '63', 'id_admin' => '611' ],
'Joe Weber' => [ 'status' => 'active', 'date' => '09/11/2014', 'serial' => '71', 'id_admin' => '666' ],
'Eric Paulsen' => [ 'status' => 'active', 'date' => '09/11/2014', 'serial' => '64', 'id_admin' => '439' ],
'India Kinniebrew' => [ 'status' => 'active', 'date' => '09/11/2014', 'serial' => '300', 'id_admin' => '639' ],
'Jason Miller' => [ 'status' => 'active', 'date' => '10/11/2014', 'serial' => '299', 'id_admin' => '660' ],
'Brian Dice' => [ 'status' => 'active', 'date' => '10/11/2014', 'serial' => '296', 'id_admin' => '642' ],
'Isaac Sanchez' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '272', 'id_admin' => '649' ],
'Joseph Buffo' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '32', 'id_admin' => '388' ],
'Alicia Bruce' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '83', 'id_admin' => '670' ],
'Albert Astorga' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '290', 'id_admin' => '638' ],
'Chris Gathof' => [ 'status' => 'active', 'date' => '11/11/2014', 'serial' => '95', 'id_admin' => '628' ],
'Keron Monk' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '269', 'id_admin' => '647' ],
'Donald Fidalgo' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '274', 'id_admin' => '645' ],
'Jason Van Buren' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '295', 'id_admin' => '566' ],
'Jose Zepeda' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '294', 'id_admin' => '604' ],
'Jason Benjoya' => [ 'status' => 'active', 'date' => '12/11/2014', 'serial' => '279', 'id_admin' => '669' ],
'Brandon Hull' => [ 'status' => 'active', 'date' => '13/11/2014', 'serial' => '7', 'id_admin' => '675' ],
'Everett Klodt' => [ 'status' => 'active', 'date' => '13/11/2014', 'serial' => '297', 'id_admin' => '651' ],
'Angel Gonzalez' => [ 'status' => 'active', 'date' => '13/11/2014', 'serial' => '284', 'id_admin' => '658' ],
'AJ Zekanoski' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '163', 'id_admin' => '688' ],
'Adam Fain' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '164', 'id_admin' => '692' ],
'Brendan Cavanaugh' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '178', 'id_admin' => '682' ],
'Jevan Vu' => [ 'status' => 'active', 'date' => '14/11/2014', 'serial' => '13', 'id_admin' => '686' ],
'Arielle Jones' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '268', 'id_admin' => '696' ],
'Michael Johnson' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '165', 'id_admin' => '693' ],
'Dawood Singleton' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '157', 'id_admin' => '536' ],
'Alexander Del Toro' => [ 'status' => 'active', 'date' => '15/11/2014', 'serial' => '162', 'id_admin' => '691' ],
'Emilio Macias' => [ 'status' => 'active', 'date' => '16/11/2014', 'serial' => '146', 'id_admin' => '701' ],
'Emma Adams' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '159', 'id_admin' => '683' ],
'John Mack' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '142', 'id_admin' => '603' ],
'Katie Aguilar' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '145', 'id_admin' => '700' ],
'David Raccasi' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '170', 'id_admin' => '709' ],
'Joseph Finnerty Dahl' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '167', 'id_admin' => '687' ],
'Brisa Pedroza' => [ 'status' => 'active', 'date' => '17/11/2014', 'serial' => '148', 'id_admin' => '704' ],
'Michael Fergus' => [ 'status' => 'active', 'date' => '18/11/2014', 'serial' => '143', 'id_admin' => '697' ],
// 'Feliccia Brown' => [ 'status' => 'active', 'date' => '18/11/2014', 'serial' => '', 'id_admin' => '718' ],
// 'Kimberly Gonzalez' => [ 'status' => '', 'date' => 'n/a', 'serial' => '104', 'id_admin' => '564' ],
'Brandon Guthrie' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '122', 'id_admin' => '571' ],
'Daniella Silva' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '156', 'id_admin' => '707' ],
'Amy Huynh' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '126', 'id_admin' => '722' ],
'Catherine Lalouh' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '234', 'id_admin' => '636' ],
'Jayson Astor' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '135', 'id_admin' => '719' ],
'Mark Phillips' => [ 'status' => 'active', 'date' => '19/11/2014', 'serial' => '212', 'id_admin' => '717' ],
'Diop Condelee' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '210', 'id_admin' => '743' ],
'Samantha Spaccasi' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '151', 'id_admin' => '706' ],
'Carlos Selva' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '174', 'id_admin' => '725' ],
'Andre Montgomery' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '131', 'id_admin' => '720' ],
'Xavier Macias' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '237', 'id_admin' => '744' ],
'Thomas Miller' => [ 'status' => 'active', 'date' => '20/11/2014', 'serial' => '282', 'id_admin' => '654' ],
'Rondell Burnham' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '113', 'id_admin' => '732' ],
'Ray Mitchell' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '281', 'id_admin' => '652' ],
'Destinee Cone' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '166', 'id_admin' => '684' ],
'Kevin Chau' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '238', 'id_admin' => '633' ],
'Trevor Lauffer' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '227', 'id_admin' => '677' ],
'Paige Butler' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '175', 'id_admin' => '736' ],
'Daniel Ayers' => [ 'status' => 'active', 'date' => '21/11/2014', 'serial' => '116', 'id_admin' => '735' ],
'Bryan Hancock' => [ 'status' => 'active', 'date' => '22/11/2014', 'serial' => '127', 'id_admin' => '614' ],
'Jason Baker' => [ 'status' => 'active', 'date' => '22/11/2014', 'serial' => '138', 'id_admin' => '711' ],
'Eleanor Christenson' => [ 'status' => 'active', 'date' => '22/11/2014', 'serial' => '283', 'id_admin' => '655' ],
'Douglas Garcia' => [ 'status' => 'active', 'date' => '23/11/2014', 'serial' => '276', 'id_admin' => '664' ],
'Mike McCarthy' => [ 'status' => 'active', 'date' => '23/11/2014', 'serial' => '181', 'id_admin' => '730' ],
'Garrett Murgatroyd' => [ 'status' => 'active', 'date' => '25/11/2014', 'serial' => '153', 'id_admin' => '613' ],
'Alex Yang' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '129', 'id_admin' => '716' ],
'Ian Bobbitt' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '136', 'id_admin' => '721' ],
'Janjay Knowlden' => [ 'status' => 'active', 'date' => '01/12/2014', 'serial' => '53', 'id_admin' => '422' ],
'Brad Pearson' => [ 'status' => 'active', 'date' => '02/12/2014', 'serial' => '107', 'id_admin' => '724' ],
'Kenny Okoduwa' => [ 'status' => 'active', 'date' => '02/12/2014', 'serial' => '200', 'id_admin' => '612' ],
'Jeremy Sundahl' => [ 'status' => 'active', 'date' => '02/12/2014', 'serial' => '173', 'id_admin' => '775' ],
'Hunter Smith' => [ 'status' => 'active', 'date' => '02/12/2014', 'serial' => '228', 'id_admin' => '753' ],
'Yamil Reynoso' => [ 'status' => 'active', 'date' => '03/12/2014', 'serial' => '154', 'id_admin' => '506' ],
'Cecilia Le' => [ 'status' => 'active', 'date' => '04/12/2014', 'serial' => '155', 'id_admin' => '554' ],
'Austin Kueffner' => [ 'status' => 'active', 'date' => '05/12/2014', 'serial' => '255', 'id_admin' => '68' ],
'Zack Valigosky' => [ 'status' => 'active', 'date' => '06/12/2014', 'serial' => '263', 'id_admin' => '329' ],
'TJ Corrigan' => [ 'status' => 'active', 'date' => '06/12/2014', 'serial' => '31', 'id_admin' => '359' ],
'Elizabeth Nyambura' => [ 'status' => 'active', 'date' => '06/12/2014', 'serial' => '222', 'id_admin' => '488' ],
'Tesiana Elie' => [ 'status' => 'active', 'date' => '06/12/2014', 'serial' => '11', 'id_admin' => '826' ],
'Mike Sheldon' => [ 'status' => 'active', 'date' => '06/12/2014', 'serial' => '240', 'id_admin' => '838' ], // -> here
'Sabrina McConnell' => [ 'status' => 'active', 'date' => '07/12/2014', 'serial' => '152', 'id_admin' => '825' ],
'Sara Radak' => [ 'status' => 'active', 'date' => '08/12/2014', 'serial' => '123', 'id_admin' => '833' ],
'Marquitta Pittman' => [ 'status' => 'active', 'date' => '08/12/2014', 'serial' => '161', 'id_admin' => '742' ],
'Malisha Parker' => [ 'status' => 'active', 'date' => '09/12/2014', 'serial' => '134', 'id_admin' => '821' ],
'Jordan Howard' => [ 'status' => 'active', 'date' => '10/12/2014', 'serial' => '139', 'id_admin' => '855' ],
'TJ Pajimola' => [ 'status' => 'active', 'date' => '10/12/2014', 'serial' => '26', 'id_admin' => '859' ],
'Martel Brown' => [ 'status' => 'active', 'date' => '11/12/2014', 'serial' => '987', 'id_admin' => '822' ],
'Evan Larson' => [ 'status' => 'active', 'date' => '12/12/2014', 'serial' => '981', 'id_admin' => '267' ],
'Niko Daza' => [ 'status' => 'active', 'date' => '11/12/2014', 'serial' => '98', 'id_admin' => '868' ],
'Angel Cuevas' => [ 'status' => 'active', 'date' => '11/12/2014', 'serial' => '118', 'id_admin' => '842' ],
'Hamid Abdasi' => [ 'status' => 'active', 'date' => '13/12/2014', 'serial' => '293', 'id_admin' => '845' ],
'Carolyn Heslop' => [ 'status' => 'active', 'date' => '13/12/2014', 'serial' => '57', 'id_admin' => '874' ],
'Tyler McDaniel' => [ 'status' => 'active', 'date' => '14/12/2014', 'serial' => '264', 'id_admin' => '386' ],
'Jason Beck' => [ 'status' => 'active', 'date' => '13/12/2014', 'serial' => '141', 'id_admin' => '831' ],
'Chloe Tawaststjerna' => [ 'status' => 'active', 'date' => '12/12/2014', 'serial' => '59', 'id_admin' => '875' ],
'Cody Cortez' => [ 'status' => 'active', 'date' => '14/12/2014', 'serial' => '226', 'id_admin' => '827' ],
'Khuder Enkh' => [ 'status' => 'active', 'date' => '15/12/2014', 'serial' => '988', 'id_admin' => '853' ],
'Hillary Thorogood' => [ 'status' => 'active', 'date' => '15/12/2014', 'serial' => '990', 'id_admin' => '856' ],
'Ahmed Mohamed' => [ 'status' => 'active', 'date' => '15/12/2014', 'serial' => '185', 'id_admin' => '880' ],
'Ryan Rubin' => [ 'status' => 'active', 'date' => '15/12/2014', 'serial' => '280', 'id_admin' => '676' ],
'Dustin Oaks' => [ 'status' => 'active', 'date' => '15/12/2014', 'serial' => '905', 'id_admin' => '872' ],
'Nguyen Tran' => [ 'status' => 'active', 'date' => '15/12/2014', 'serial' => '902', 'id_admin' => '877' ],
'Ben Huber' => [ 'status' => 'active', 'date' => '16/12/2014', 'serial' => '999', 'id_admin' => '870' ],
'Elliot Thompson' => [ 'status' => 'active', 'date' => '16/12/2014', 'serial' => '996', 'id_admin' => '864' ],
'Kevin Stephens' => [ 'status' => 'active', 'date' => '17/12/2014', 'serial' => '986', 'id_admin' => '852' ],
'Summer Rogers' => [ 'status' => 'active', 'date' => '17/12/2014', 'serial' => '186', 'id_admin' => '873' ],
'Andrew Kowalski' => [ 'status' => 'active', 'date' => '17/12/2014', 'serial' => '267', 'id_admin' => '887' ],
'Roy Klump' => [ 'status' => 'active', 'date' => '18/12/2014', 'serial' => '989', 'id_admin' => '854' ],
'Frank Burmeister' => [ 'status' => 'active', 'date' => '18/12/2014', 'serial' => '183', 'id_admin' => '865' ],
'Stephan Goodwin' => [ 'status' => 'active', 'date' => '18/12/2014', 'serial' => '239', 'id_admin' => '879' ],
'Leosha Jackson' => [ 'status' => 'active', 'date' => '18/12/2014', 'serial' => '117', 'id_admin' => '840' ],
'Nick Cerini' => [ 'status' => 'active', 'date' => '07/01/2015', 'serial' => '128', 'id_admin' => '878' ],
'Jaime Herrera' => [ 'status' => 'active', 'date' => '07/01/2015', 'serial' => '927', 'id_admin' => '871' ],
		];

		$customers = Crunchbutton_Pexcard_Card::card_list();

		foreach( $cards as $name => $card ){

			$saved = false;

			if( $card[ 'status' ] == 'active' && is_numeric( $card[ 'serial' ] ) && strlen( $card[ 'date' ] ) == 10 ){

				$date = explode( '/', $card[ 'date' ] );
				$date = $date[ '2' ] . '-' . $date[ '1' ] . '-' . $date[ '0' ] . ' 00:00:01';

				$admin = Admin::o( $card[ 'id_admin' ] );

				if( $admin->id_admin ){

					$pexcard = Cockpit_Admin_Pexcard::q( 'SELECT * FROM admin_pexcard WHERE card_serial = ' . intval( $card[ 'serial' ] ) );

					if( $pexcard->id_admin_pexcard ){
						if( $pexcard->id_admin != $admin->id_admin ){
							// die( $name );
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
		die( 'hard' );
	}

	public function processAndValidateCSVData(){

		$data = "David Klumpp,USC,$0,off,n/a,
		Bao Truong,Holy Cross,$0,active,22/10/2014,4
		Brandon Shundoff,U-Nebraska Lincoln,$0,active,23/10/2014,8
		Monte J. Ely,Michigan State,$0,active,25/10/2014,5
		Parsa Parirokh,UCSD,$250,active,29/10/2014,9
		Devin Conatser,U-Indiana Blommington,$0,active,29/10/2014,10
		Chris Tolbert,UCLA,$0,active,29/10/2014,21
		Francisco Vasquez,USC,$0,active,29/10/2014,14
		Simo Aichouri,Cal Poly SLO,$250,active,30/10/2014,28
		Ryan Nunley,Cal Poly SLO,$0,active,30/10/2014,12
		Tom Fekete,USC,$0,active,19/11/2014,195
		Aaron Kim,U-Arizona,$0,active,02/11/2014,25
		Sarah Goldstein,Colgate,$0,active,01/11/2014,56
		Jeff Jacquay,Cal Poly SLO,$0,off,n/a,30
		Nick Klimek,U-Indiana Blommington,$0,active,01/11/2014,79
		Jane Vezina,St Olaf,$0,active,02/11/2014,88
		Jacob Lubben,U-Illinois Urbana,$0,active,02/11/2014,51
		John Barry,U-Indiana Blommington,$0,active,03/11/2014,
		Greer Bohanon,USC,$0,active,03/11/2014,68
		Matthew Trnka,UCLA,$0,active,03/11/2014,90
		Luke Schmiegel,Virginia Tech,$0,active,03/11/2014,99
		Colton Reed,U-Indiana Blommington,$0,active,04/11/2014,66
		Zsatasia Green,Washington State,$0,active,04/11/2014,27
		Alec Root,UNC,$0,active,04/11/2014,61
		Emory Johnson,U-Arizona,$0,active,07/11/2014,84
		Adam Bezemek,Michigan State,$0,active,04/11/2014,16
		Natalie Santa,Michigan State,$0,active,04/11/2014,76
		Abram Schroeder,U-Indiana Blommington,$0,active,05/11/2014,67
		Precious Jones,U-Illinois Urbana,$0,active,05/11/2014,47
		Casey Domek,U-Indiana Bloomington,$0,active,06/11/2014,85
		Robert Warren,Michigan State,$0,active,06/11/2014,78
		Sara Lind,St Olaf,$0,active,06/11/2014,86
		Josh Peterson,U-Arizona,$0,active,06/11/2014,91
		James Gwinn,Washington State,$0,active,06/11/2014,73
		Deshawn Alan,Michigan State,$0,active,06/11/2014,74
		Jesse Little,Washington State,$0,active,06/11/2014,24
		Kahealani Alexander,Washington State,$0,active,06/11/2014,22
		Perry Thomas,U-Oregon,$0,active,07/11/2014,94
		Steven Frasica,UNC,$0,active,07/11/2014,58
		Jamie Jackson,Dayton,$0,active,07/11/2014,29
		Zach Sattinger,U-Arizona,$0,active,08/11/2014,70
		SunSun Gan,U-Oregon,$0,active,08/10/2014,63
		Joe Weber,U-Indiana Bloomington,$0,active,09/11/2014,71
		Eric Paulsen,UNC,$250,active,09/11/2014,64
		India Kinniebrew,USC,$0,active,09/11/2014,300
		Jason Miller,U-Oregon,$0,active,10/11/2014,299
		Brian Dice,Washington State,$0,active,10/11/2014,296
		Isaac Sanchez,U-Illinois Urbana,$0,active,11/11/2014,272
		Joseph Buffo,Dayton,$0,active,11/11/2014,32
		Alicia Bruce,U-Oregon,$0,active,11/11/2014,83
		Albert Astorga,USC,$0,active,11/11/2014,290
		Chris Gathof,Michigan State,$0,active,11/11/2014,95
		Keron Monk,Ohio State,$0,active,12/11/2014,269
		Donald Fidalgo,Yale,$0,active,12/11/2014,274
		Jason Van Buren,UCLA,$0,active,12/11/2014,295
		Jose Zepeda,USC,$0,active,12/11/2014,294
		Jason Benjoya,U-Illinois Urbana,$0,active,12/11/2014,279
		Brandon Hull,U-Indiana Bloomington,$0,active,13/11/2014,7
		Everett Klodt,Washington State,$0,active,13/11/2014,297
		Angel Gonzalez,U-Oregon,$0,active,13/11/2014,284
		AJ Zekanoski,Cal Poly SLO,$0,active,14/11/2014,163
		Adam Fain,Cal Poly SLO,$0,active,14/11/2014,164
		Brendan Cavanaugh,U-Arizona,$0,active,14/11/2014,178
		Jevan Vu,Cal Poly SLO,$0,active,14/11/2014,13
		Arielle Jones,UNC,$0,active,15/11/2014,268
		Michael Johnson,UNC,$0,active,15/11/2014,165
		Dawood Singleton,UC Riverside,$0,active,15/11/2014,157
		Alexander Del Toro,UCR,$0,active,15/11/2014,162
		Emilio Macias,Oregon,$0,active,16/11/2014,146
		Emma Adams,Virginia Tech,$0,active,17/11/2014,159
		John Mack,Oregon,$0,active,17/11/2014,142
		Katie Aguilar,Oregon,$0,active,17/11/2014,145
		David Raccasi,Arkansas,$0,active,17/11/2014,170
		Joseph Finnerty Dahl,Virginia Tech,$0,active,17/11/2014,167
		Brisa Pedroza,Oregon,$0,active,17/11/2014,148
		Michael Fergus,Oberlin,$0,active,18/11/2014,143
		Feliccia Brown,Oberlin,0,active,18/11/2014,
		Kimberly Gonzalez,Crunchbutton,$0,off,n/a,104
		Brandon Guthrie,U-Nebraska Lincoln,0,active,19/11/2014,122
		Daniella Silva,UC Riverside,$0,active,19/11/2014,156
		Amy Huynh,UCLA,$0,active,19/11/2014,126
		Catherine Lalouh,U-Arizona,$0,active,19/11/2014,234
		Jayson Astor,U-Arizona,0,active,19/11/2014,135
		Mark Phillips,UCLA,$250,active,19/11/2014,212
		Diop Condelee,Michigan State,$0,active,20/11/2014,210
		Samantha Spaccasi,Oberlin,$0,active,20/11/2014,151
		Carlos Selva,Arkansas,$0,active,20/11/2014,174
		Andre Montgomery,Arizona,$0,active,20/11/2014,131
		Xavier Macias,Arizona,$0,active,20/11/2014,237
		Thomas Miller,Oregon,$0,active,20/11/2014,282
		Rondell Burnham,Yale,$0,active,21/11/2014,113
		Ray Mitchell,UNC,$0,active,21/11/2014,281
		Destinee Cone,Washington State,$0,active,21/11/2014,166
		Kevin Chau,Arizona,$0,active,21/11/2014,238
		Trevor Lauffer,Oberlin,$250,active,21/11/2014,227
		Paige Butler,Arkansas,$0,active,21/11/2014,175
		Daniel Ayers,Oregon,$250,active,21/11/2014,116
		Bryan Hancock,Indiana,$0,active,22/11/2014,127
		Jason Baker,Cal Poly SLO,$0,active,22/11/2014,138
		Eleanor Christenson,Oregon,$250,active,22/11/2014,283
		Douglas Garcia,USC,$0,active,23/11/2014,276
		Mike McCarthy,UCLA,$0,active,23/11/2014,181
		Garrett Murgatroyd,Oregon,$0,active,25/11/2014,153
		Alex Yang,Virginia Tech,0,active,01/12/2014,129
		Ian Bobbitt,Indiana,$0,active,01/12/2014,136
		Janjay Knowlden,Illinois,$0,active,01/12/2014,53
		Daniel Camargo,Testing,0,active,01/12/2014,42
		Brad Pearson,Arkansas,$0,active,02/12/2014,107
		Kenny Okoduwa,UCLA,$0,active,02/12/2014,200
		Jeremy Sundahl,Cal Poly SLO,$0,active,02/12/2014,173
		Hunter Smith,Washington State,$0,active,02/12/2014,228
		Yamil Reynoso,Yale,$0,active,03/12/2014,154
		Cecilia Le,Riverside,$250,active,04/12/2014,155
		Austin Kueffner,UNC,$0,active,05/12/2014,255
		Zack Valigosky,Dayton,$0,Active,06/12/2014,263
		TJ Corrigan,Dayton,$0,Active,06/12/2014,31
		Elizabeth Nyambura,Washington State,$0,Active,06/12/2014,222
		Tesiana Elie,UC San Diego,$0,Active,06/12/2014,11
		Mike Sheldon,Arizona,$0,Active,06/12/2014,240
		Sabrina McConnell,Oberlin,$0,Active,07/12/2014,152
		Sara Radak,USC,$0,Active,08/12/2014,123
		Marquitta Pittman,Yale,$0,Active,08/12/2014,161
		Malisha Parker,Virginia Tech,$0,Active,09/12/2014,134
		Jordan Howard,Yale,$0,Active,10/12/2014,139
		TJ Pajimola,Washington State,$0,Active,10/12/2014,26
		Martel Brown,Oberlin,$0,Active,11/12/2014,987
		Evan Larson,Washington State,$0,Active,12/12/2014,981
		Niko Daza,UC Irvine,$0,Active,11/12/2014,98
		Angel Cuevas,UC Riverside,$250,Active,11/12/2014,118
		Hamid Abdasi,USC,$0,Active,13/12/2014,293
		Carolyn Heslop,Colgate,$0,Active,13/12/2014,57
		Tyler McDaniel,Dayton,$0,Active,14/12/2014,264
		Jason Beck,Riverside,$0,Active,13/12/2014,141
		Chloe Tawaststjerna,Colgate,$0,Active,12/12/2014,59
		Cody Cortez,Washington State,$0,Active,14/12/2014,226
		Khuder Enkh,UCSD,$0,Active,15/12/2014,988
		Hillary Thorogood,USC,$0,Active,15/12/2014,990
		Ahmed Mohamed,Riverside,$0,Active,15/12/2014,185
		Ryan Rubin,Indiana,$0,Active,15/12/2014,280
		Dustin Oaks,Riverside,$250,Active,15/12/2014,905
		Nguyen Tran,UCSD,$0,Active,15/12/2014,902
		Ben Huber,Virginia Tech,$0,Active,16/12/2014,999
		Elliot Thompson,UCSD,$0,Active,16/12/2014,996
		Kevin Stephens,Riverside,0,Active,17/12/2014,986
		Summer Rogers,Riverside,0,Active,17/12/2014,186
		Andrew Kowalski,Dayton,0,Active,17/12/2014,267
		Roy Klump,Arizona,$0,Active,18/12/2014,989
		Frank Burmeister,Riverside,$0,Active,18/12/2014,183
		Stephan Goodwin,Oberlin,$0,Active,18/12/2014,239
		Leosha Jackson,Riverside,$0,Active,18/12/2014,117
		Nick Cerini,Cal Poly SLO,$0,Active,07/01/2015,128
		Jaime Herrera,UNC,0,Active,07/01/2015,927";

		$data = explode( "\n" , $data );

		foreach( $data as $driver ){

			$driver = explode( ",", trim( $driver ) );
			$active = ( strtolower( $driver[ 3 ] ) == 'active' ? 'active' : '' );
			$name = $driver[ 0 ];
			$serial = $driver[ 5 ];
			$date = $driver[ 4 ];

			$driver = null;
			$admins = Admin::q( 'SELECT * FROM admin WHERE name = "' . $name . '"' );
			if( $admins->count() != 1 ){
				$actives = 0;
				foreach( $admins as $admin ){
					if( $admin->active ){
						$driver = $admin;
						$actives++;
					}
					if( $actives > 1 ){
						echo '<pre>';var_dump( $admins->count(), $name, $serial, $active );exit();
					}
				}
			} else {
				$driver = $admins->get( 0 );
			}

			if( $driver ){
				echo "'{$name}' => [ 'status' => '{$active}', 'date' => '{$date}', 'serial' => '{$serial}', 'id_admin' => '{$driver->id_admin}' ],\n";
			} else {
				echo '<pre>';var_dump( $name, $serial, $active );exit();
			}
		}
	}
}