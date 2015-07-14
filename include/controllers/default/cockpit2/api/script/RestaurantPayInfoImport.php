<?php

// Prep for Settlement Test #3516
class Controller_Api_Script_RestaurantPayInfoImport extends Crunchbutton_Controller_RestAccount {

	public function init() {
		die('remove this line');
		Controller_Api_Script_RestaurantPayInfoImport::method_init();
	}

	public function method_init(){
		$data = Controller_Api_Script_RestaurantPayInfoImport::method_data();
		$data = explode( "\n",  $data );

		$_notifications = [];

		foreach ( $data as $row ) {

			$_notification = ': none';

			$row = explode( ";", $row );
			$restaurant = [];
			$id_restaurant = intval( trim( $row[ 0 ] ) );
			$name = trim( $row[ 1 ] );
			$email = trim( $row[ 2 ] );
			$method = strtolower( trim( $row[ 3 ] ) );

			// Get the current restaurant's payment type
			$payment = Crunchbutton_Restaurant_Payment_Type::q( 'SELECT * FROM restaurant_payment_type WHERE id_restaurant = ? ORDER BY id_restaurant_payment_type DESC LIMIT 1', [$id_restaurant]);
			if( !$payment->id_restaurant_payment_type ){
				$payment = new Crunchbutton_Restaurant_Payment_Type;
			}
			$payment->id_restaurant = $id_restaurant;

			if( $method == 'email' ){
				$payment->method = 'email';
				if( $email != '' ){
					if( filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
						$payment->summary_email = $email;
					}
				} else {
					$email = Crunchbutton_Notification::q( 'SELECT * FROM notification WHERE id_restaurant = ? AND active = true AND type = ? LIMIT 1', [$id_restaurant, Crunchbutton_Notification::TYPE_EMAIL]);
					if( $email->id_notification ){
						$payment->summary_email = $email->value;
					}
				}
				$_notification = ' email: ' . $payment->summary_email;
			}

			if( $method == 'fax' ){
				$payment->method = 'fax';
				$fax = Crunchbutton_Notification::q( 'SELECT * FROM notification WHERE id_restaurant = ? AND active = true AND type = ? LIMIT 1', [$id_restaurant, Crunchbutton_Notification::TYPE_FAX]);
				if( $fax->id_notification ){
					$payment->summary_fax = $fax->value;
				}
				$_notification = ' fax: ' . $payment->summary_fax;
			}

			$payment->save();
			$_notifications[ $id_restaurant ] = $name . ' (' . $id_restaurant . ') ' . $_notification . "\n";
		}

		foreach( $_notifications as $notification ){
			echo $notification;
		}

		die('done!');

	}

	public function payment_init(){

		$data = Controller_Api_Script_RestaurantPayInfoImport::payment_data();
		$data = explode( "\n",  $data );

		$_errors = [];
		$_saved = [];
		$_nope = [ 	'summary' => [],
								'method' => [],
								'tax_id' => [],
								'legal_name' => [],
								'check_address' => [] ];

		foreach ( $data as $row ) {

			$errors = [];

			$row = explode( ";", $row );
			$restaurant = [];
			$id_restaurant = intval( trim( $row[ 0 ] ) );
			$email = trim( $row[ 2 ] );
			$address = trim( $row[ 6 ] );
			$legal_name_payment = trim( $row[ 1 ] );
			$tax_id = trim( $row[ 4 ] );
			$method = strtolower( trim( $row[ 3 ] ) );

			// Get the current restaurant's payment type
			$payment = Crunchbutton_Restaurant_Payment_Type::q( 'SELECT * FROM restaurant_payment_type WHERE id_restaurant = ? ORDER BY id_restaurant_payment_type DESC LIMIT 1', [$id_restaurant]);
			if( !$payment->id_restaurant_payment_type ){
				$payment = new Crunchbutton_Restaurant_Payment_Type;
			}
			$payment->id_restaurant = $id_restaurant;

			// check and fill all the adresses fields!!!
			if( $address != '' ){
				$_address = explode( ',', $address );
				if( count( $_address ) == 4 ){
					$payment->check_address = trim( $_address[ 0 ] );
					$payment->check_address_city = trim( $_address[ 1 ] );
					$payment->check_address_state = trim( $_address[ 2 ] );
					$payment->check_address_zip = filter_var( trim( $_address[ 3 ] ), FILTER_SANITIZE_NUMBER_INT );
					$payment->check_address_country = 'USA';
					if( strlen( $payment->check_address_state ) != 2 ){
						$errors[] = 'State: ' . $payment->check_address_state;
					}
					if( intval( $payment->check_address_zip ) < 0 ){
						$errors[] = 'Zip: ' . $payment->check_address_zip;
					}
				} else {
					$errors[] = 'Missing Address';
				}
			} else {
				$errors[] = 'Missing Address';
			}

			if( $method != '' && ( $method == Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_CHECK || $method == Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_DEPOSIT ) ){
				$payment->method = $method;
			} else {
				$errors[] = 'Missing Payment Method: ' . $method;
			}

			if( $email != '' ){
				if( filter_var( $email, FILTER_VALIDATE_EMAIL ) ){
					$payment->summary_email = $email;
					$payment->summary_method = 'email';
				} else {
					$errors[] = 'Email: ' . $email;
				}
			}

			if( $tax_id != '' ){
				$tax_id = filter_var( $tax_id, FILTER_SANITIZE_NUMBER_INT );
				$tax_id = str_replace( '-', '', $tax_id );
				$tax_id = str_replace( '+', '', $tax_id );
				if( strlen( $tax_id ) == 9 ){
					$payment->tax_id = $tax_id;
				} else {
					$errors[] = 'Invalid Tax ID: ' . $tax_id;
				}
			}

			if( $legal_name_payment != '' ){
				$payment->legal_name_payment = $legal_name_payment;
			}

			$shouldSave = true;

			if( count( $errors ) > 0 ){
				$_restaurant = '';
				// $shouldSave = false;
				$_restaurant .= $legal_name_payment . ' ID:' . $id_restaurant;
				foreach( $errors as $error ){
					$_restaurant .= "\n" . $error ;
				}
				$_restaurant .= "\n--------------------------------\n\n";
				$_errors[] = $_restaurant;
			}

			if( $shouldSave ){
				$payment->save();
				$_saved[] = $legal_name_payment;
			}

			if( !$payment->method ){
				$_nope[ 'method' ][] = $legal_name_payment;
			}
			if( !$payment->summary ){
				$_nope[ 'summary' ][] = $legal_name_payment;
			}
			if( !$payment->tax_id ){
				$_nope[ 'tax_id' ][] = $legal_name_payment;
			}
			if( $payment->method == 'check' && !$payment->check_address ){
				$_nope[ 'check_address' ][] = $legal_name_payment;
			}
		}

		echo 'Method: ' . count( $_nope[ 'method' ] ) . "\n";
		foreach ( $_nope[ 'method' ] as $restaurant ) {
			echo $restaurant. ':' . $restaurant->id_restaurant . "\n";
		}
		echo "\n--------------------------------\n\n";

		echo 'Summary: ' . count( $_nope[ 'summary' ] ) . "\n";
		foreach ( $_nope[ 'summary' ] as $restaurant ) {
			echo $restaurant. ':' . $restaurant->id_restaurant . "\n";
		}
		echo "\n--------------------------------\n\n";

		echo 'Tax Id: ' . count( $_nope[ 'tax_id' ] ) . "\n";
		foreach ( $_nope[ 'tax_id' ] as $restaurant ) {
			echo $restaurant. ':' . $restaurant->id_restaurant . "\n";
		}
		echo "\n--------------------------------\n\n";

		echo 'Address: ' . count( $_nope[ 'check_address' ] ) . "\n";
		foreach ( $_nope[ 'check_address' ] as $restaurant ) {
			echo $restaurant. ':' . $restaurant->id_restaurant . "\n";
		}
		echo "\n--------------------------------\n\n";

		echo 'SAVED: ' . count( $_saved ) . "\n";
		foreach ( $_saved as $saved ) {
			echo $saved. "\n";
		}

		echo "\n--------------------------------\n\n";
		echo 'ERRORS: ' . count( $_errors ) . "\n";
		foreach ( $_errors as $error ) {
			echo $error. "\n";
		}
		echo "\n--------------------------------\n\n";

	}

	public function method_data(){
		return "60;1 Fish 2 Fish;biao2423@hotmail.com;fax;
1;Alpha Delta Pizza;;none;
64;Angkor;;fax;
83;Better Burger Company;;unknown;
43;Bistro Med;;fax;
2;Brick Oven Pizza;;none;
59;Brickhouse Kitchen;;fax;
41;Café Istanbul;;fax;
47;Chap's Grille;;fax;
9;China King;;fax;
12;Chinese Iron Wok;;email;
63;Cilantro Mexican Grill;;fax;
44;Est Est Est Pizza;;fax;
46;Fresh Taco;;fax;
37;George's;;fax;
17;Golden Crust;;fax;
13;Hercules Mulligan;;unknown;
20;Kabob and Curry;;fax;
148;Kabob and Curry CBD;;fax;
40;Kitchen No. 1;;fax;
38;Los Cuates;;fax;
80;Louis;;unknown;
45;Main Garden;;fax;
52;Marco Polo;;unknown;
61;Marvelous Pizza;;fax;
75;Meeting Street Cafe;edwinsosa40@gmail.com;email;
29;Oasis Grill;;none;
62;Pizza D'oro;;fax;
25;Pizza Pie-er;;fax;
19;Providence Calzone;;unknown;
56;Sacrificial Lamb;;fax;
28;Shanghai;;fax;
27;Sitar;info@sitarnewhaven.com;fax;
14;Sushi Express;;email;
65;Sushi Mizu;;fax;
21;s'Wings;;fax;
51;Tandoor;;unknown;
5;Thai Pan;;fax;
42;Urfa Tomato Kabob;;fax;
22;Vasilio's Pizza;;unknown;
36;Wingo's;;fax;
18;Wings Over Providence;;none;
39;Wisey's;;fax;
8;Zaroka;;fax;
68;Geoff's Sandwiches;cafezogandgeoffs@gmail.com;fax;
69;Bagel Gourmet Ole;;unknown;
71;Mirano Pizza;;fax;
76;Dial-a-Pizza;;fax;
77;Falafel Corner;;fax;
67;Banana Leaves;;fax;
41;Cafe Istanbul;;fax;
78;Sicilia's Pizzeria;;fax;
79;Bagel Gourmet (Brook St);;unknown;
70;Nancy's Fancies Cupcakes;;email;
80;Loui's;;unknown;
74;Spicy With Delivery;;;
73;Abyssinia Ethiopian;;fax;
86;Wise Guys Deli;Jessica@corpgfm.com;email;
82;Pho Paradise;;unknown;
99;Pizzoli's;;Fax;
110;Sahara Mediterranean;colton.jang@yale.edu;fax;
106;Siam Best Thai Cuisine;;fax;
98;Boulder Salad;;Fax;
103;Himalayan Cafe;;Fax;
94;Naraya Thai & Sushi;;Fax;
104;Numero Uno Pizza;;Fax;
111;Veggie Fun;;fax;
115;Yoo Sushi;;fax;
108;Leaf Organics;rod@leaforganics.com;Fax;
118;Marla's Cafe;;fax;
120;Munch;;fax;
121;My Daddy's Grille;dbog96@aol.com;none;
122;San Marino Ristaurante;sanmarinosoho@gmail.com;fax;
91;Milanos Pizzeria;;fax;
128;Li Li Wok;;fax;
85;Jordan's Hot Dogs & Mac;;none;
133;La Paloma;;fax;
134;The Wok;;Fax;
135;Big Tony's Pizza;;fax;
123;Cilantro North Providence;;fax;
142;Little Thai Kitchen;;fax;
143;Tomatillo Taco;tomatillo.taco@gmail.com;fax;
140;Weed World Candies;;unknown;
146;Jeera Thai;dara_2525@hotmail.com;Fax;
154;Late Night Delivery;habchisaad9@aol.com;fax;
170;Quiznos;;fax;
158;China Garden;hongchan68@hotmail.com;fax;
156;Manny and Olgas;;fax;
173;Banana Delivery;;;
159;New York Pizza Depot;dtelemaco@comcast.net;fax;
161;Pino's;;fax;
185;Fresh in the box;;fax;
184;Taj India Palace;tajindiapalace@yahoo.com;fax;
195;El Huarique;elhuariqueperu@hotmail.com;fax;
178;Trio House;ray@triohouse.com;fax;
176;Thai Corner Food Express;aritsaelliott@att.net;fax;
168;China Express;;email;
164;Giovanni's Pizzeria;;email;
179;Rio Grande Tex- Mex Grille;;email;
165;Tony's Pizzeria;;unknown;
188;Uncle Tony's Pizza;;fax;
201;FoBoGro;;unknown;
196;Hannaford's;;;
197;Hill Party ;;;
181;Mitsuba;;email;
179;Rio Grande Tex-Mex Grille;;email;
167;Istanblue;;fax;
166;The Pizza 7;;fax;
219;Subway;;;
218;Dunkin' Donuts;;;
213;Hill Tobacco;;;
198;Hill-Help;;;
180;McDonalds;;;
210;Benny's Tacos;bennystacos@aol.com;fax;
240;Five Guys ;;;
222;Panera Bread;;;
173;Free Banana Delivery ;;;
194;Pasta Roma;pastaromafig@sbcglobal.net;fax;
244;Geo's Organic Coffee & Fine Tea;;fax;
241;Viztango;titorivera5@aol.com;fax;
193;Taste of the Islands;taste_of_the_islands@yahoo.com;Email;
220;Frankie & Johnnie's Pizza;nasserahdoot@gmail.com;fax;
250;Free Crunch;;;
235;I am BC Gorilla;;;
248;G-DUB WINGZZZZ;;;
251;TG Express;;Fax;
223;Mayura Amrit;pputhe@yahoo.com (might be pputne@yahoo.com;fax;
256;Mayura Restaurant;pputhe@yahoo.com (might be pputne@yahoo.com;fax;
254;Chipotle DELIVERED;;;
257;Ham-Mart;;;
258;Puppy Visit;;;
205;Lewiston House of Pizza;JKoutsikos@gmail.com;Fax;
268;Morfia's Ribs and Pies;;fax;
262;Pure Thai;pat_alen@hotmail.com;unknown;
225;Chopsticks;jenny@chopsticks-restaurant.com;email;
274;Moes;;;
276;KFC;;;
277;Joe & Mimi's Pizzeria;joeandmimis@gmail.com;email;
297;22 Utica Street Cafe;;fax;
281;Rusch's Bar & Grill;ruschsbarandgrill@gmail.com;email;
278;Chipotle;;;
279;Panera Bread;;;
280;7 Nana;;;
290;McDonalds;;;
291;Subway;;;
293;Dunkin Donuts;;;
302;McDonalds;;;
307;Subway;;;
308;Taco Bell;;;
309;Arby's;;;
310;Culver's;;;
311;Chipotle;;;
313;Al's Burger Shack;alsburgershack@gmail.com;email;
316;Artisan Pizza;artisanpizzach@gmail.com;email;
317;Sakura;sakuraxpress110@gmail.com;email;
318;McAlister's Deli;;fax;
260;Royal Indian Grill;bhavjotchadha@gmail.com;email;
261;Chipotle;;;
263;Panera Bread;;;
305;Pizza Hut;;;
319;Hamilton Eatery;;;
320;Drive for CB;;;
321;Drive for CB;;;
322;Drive for CB;;;
378;Wawa;;;
259;Chipotle;;;
264;Fresh to Order;;;
295;Sweetgreen;;;
380;In-N-Out;;;
383;BurgerFi;;;
385;Top This;pillyrivers@yahoo.com;email;
389;Ken's Quick-E Mart;;none;
392;Chipotle;;;
395;Chipotle;;;
396;Chick-fil-A;;;
397;Panda Express;;;
324;Drive or Bike for us;;;
404;Chipotle;;;
386;Zoe's Kitchen;;;
403;Wawa;;;
325;We Need Delivery Bikers!;;;
327;We Need Drivers to Launch!;;;
406;Subway;;;
413;Spudnuts Donuts;;;
414;Ground Zero Performance Cafe;;;
415;Drive for CB;;;
416;Pizza Studio;;;
421;Bike for us!;;;
402;Fitzgerald's;sales@fitzgeraldschapelhill.com;email;
408;New Buffet;;;
409;Panera Bread;;;
265;Ruben's Mexican Restaurant;;fax;
394;Healthy Fresh;;fax;
410;Chipotle;;;
435;Raising Cane's;;;
447;McDonalds;;;
412;Uncle Darrow's;;fax;
446;Lime Fresh Mexican Grill;LF6330@limefmg.com;email;
452;Hamilton Whole Foods;hamiltonwholefoodscb@gmail.com ;email;
453;VJ's;vjsrestaruant@gmail.com  ;fax;
461;Taco Bell;;;
467;old Top This;mike.itayem@yahoo.com;email;
411;Launch Crunchbutton Yourself;;;
422;Drive For Us Next Semester;;;
454;Drive for Crunchbutton;;;
460;Raising Cane's;;;
468;Red Onion;;fax;
465;Char;;fax;
462;Parthenon;;fax;
463;California Kabob Kitchen;ckkrestaurant@gmail.com;fax;
438;Food Haus;foodhauscafe@gmail.com;email;
480;Klondike Cafe;KlondikeCafeBoone@yahoo.com;Email;
478;Cilantro's Mexican Grille;cilantros@live.com;Email;
423;Opens at 6pm!;;;
470;drive for us;;;
488;old Benny's Tacos;bennystacos@aol.com;;
469;Chipotle;;;
429;California Gogi Korean Grill;;;
479;Comeback Shack;;;
485;Budacki's;eve@budackishotdog.com;Email;
492;Budacki's;eve@budackishotdog.com;email;
493;Salad Farm;;fax;
444;Chichen Itza;gcetina@chichenitzarestaurant.com;email;
498;Jack-In-The-Box;;;
500;McDonalds;;;
505;Drive for us!;;;
457;Cougar Country Drive In;;none;
474;Drive for Us;;;
512;In-N-Out;;;
513;McDonalds;;;
511;Chipotle;;;
497;Nature's Brew;naturesbreworders@gmail.com;email;
525;Taco Bell;;;
551;old Main Garden;;;
509;Arby's;;;
515;Taco Bell;;;
533;Chipotle;;;
539;Penn Station East Coast Subs;;;
541;Buffalo Wild Wings;;;";
	}

	public function payment_data(){
		return "60;1 Fish 2 Fish;biao2423@hotmail.com;deposit;272121017;;;
1;Alpha Delta Pizza;;deposit;;;;
64;Angkor;;deposit;;;;
83;Better Burger Company;;check;;BBC Foods Inc.;217 Thayer Street, Providence, RI, 02906;
43;Bistro Med;;Check;;;736 6th Street NW, Washington, DC, 20001;
2;Brick Oven Pizza;;check;;;122 Howe Street, New Haven, CT, 06511;
59;Brickhouse Kitchen;;check;;The Brickhouse Kitchen;826 Hampton Dr, Venice, CA, 90291;
41;Café Istanbul;;Check;;;2475 18th Street NW, Washington, DC, 20009;
47;Chap's Grille;;check;;;1174 Chapel Street, New Haven, CT, 06511;
9;China King;;check;;;;
12;Chinese Iron Wok;;deposit;;;;
63;Cilantro Mexican Grill;;check;;;127 Weybosset Street, Providence, RI, 02903;
44;Est Est Est Pizza;;deposit;455278531;LA-ZE, LLC;;
46;Fresh Taco;;deposit;2398014001;;39 Elm Street, New Haven, CT, 06510;
37;George's;;check;;;1205 28th Street N.W., Washington, DC, 20007;
17;Golden Crust;;check;;;228 Oakland Avenue, Providence, RI, 02909;
13;Hercules Mulligan;;check;;;272 Thayer Street, Providence, RI, 02906;
20;Kabob and Curry;;check;;India House, Inc.;261 Thayer Street, Providence, RI, 02906;
148;Kabob and Curry CBD;;check;;India House, Inc.;261 Thayer Street, Providence, RI, 02906;
40;Kitchen No. 1;;Check;;;3208 O St. NW, Washington, DC, 20007;
38;Los Cuates;;Check;;;1564 Wisconsin Ave. NW, Washington, DC, 20007;
80;Louis;;check;;;;
45;Main Garden;;check;46-2716366;Jian Hua Zhu;376 Elm St, New Haven, CT, 06511;
52;Marco Polo;;check;;Marco Polo Pizza;55 Crown St, New Haven, CT, 06510;
61;Marvelous Pizza;;Check;;Pizza Art & Design Inc.;941 H Street Northeast, Washington, DC, 20002;
75;Meeting Street Cafe;edwinsosa40@gmail.com;deposit;;Odirin Adidi ;220 Meeting Street, Providence, RI, 02906;
29;Oasis Grill;;deposit;3658134602;Almuthaseb Enterprises Inc.;;
62;Pizza D'oro;;deposit;271627747;;;
25;Pizza Pie-er;;deposit;;;;
19;Providence Calzone;;deposit;;;;
56;Sacrificial Lamb;;Check;;Omar H Bedane LLC;1704 R St. N.W, Washington, DC, 20009;
28;Shanghai;;check;;;272 Thayer Street, Providence, RI, 02906;
27;Sitar;info@sitarnewhaven.com;deposit;203203190;Sitar Indian Restaurant;45 Grove Street, New Haven, CT, 06511;
14;Sushi Express;;check;;;283 Thayer Street, Providence, RI, 02906;
65;Sushi Mizu;;deposit;57021966001;;47 Whalley Avenue, New Haven, CT, 06511;
21;s'Wings;;check;;;280 Crown Street, New Haven, CT, 06510;
51;Tandoor;;check;;;1226 Chapel St, New Haven, CT, 06511;
5;Thai Pan;;check;;Thai Pan Asian;1150 Chapel Street, New Haven, CT, 06511;
42;Urfa Tomato Kabob;;Check;;;740 6th Street NW, Washington, DC, 20001;
22;Vasilio's Pizza;;check;;;;
36;Wingo's;;deposit;611429384;;;
18;Wings Over Providence;;check;;;725 Hope Street, Providence, RI, 02906;
39;Wisey's;;Check;;;1440 Wisconsin Ave. NW, Washington, DC, 20007;
8;Zaroka;;deposit;;Zaroka Bar & Restaurant;;
68;Geoff's Sandwiches;cafezogandgeoffs@gmail.com;deposit;;Geoff's Superlative Sandwiches;163 Benefit Street, Providence, RI, 02903;
69;Bagel Gourmet Ole;;deposit;753001668;Bagel Gourmet;250 Brook Street, Providence, RI, 02906;
71;Mirano Pizza;;deposit;454199473;Mirano Grill and Pizza;600 Douglas Avenue, Providence, RI, 02908;
76;Dial-a-Pizza;;check;;;147 Beacon St., Somerville, MA, 02143;
77;Falafel Corner;;deposit;;;;
67;Banana Leaves;;check;;;2020 Florida Avenue Northwest, Washington, DC, 20009;
41;Cafe Istanbul;;check;;;2475 18th Street Northwest, Washington, DC, 20009;
78;Sicilia's Pizzeria;;deposit;800433320;Sicilia's Pizzeria Inc.;;
79;Bagel Gourmet (Brook St);;deposit;753001668;Bagel Gourmet;250 Brook Street, Providence, RI, 02906;
70;Nancy's Fancies Cupcakes;;check;;Nancy's Fancies;294 Atwells Ave, Providence, RI, 02903;
80;Loui's;;check;;Loui's Restaurant;286 Brook Street, Providence, RI, 02906;
74;Spicy With Delivery;;n/a;;;;
73;Abyssinia Ethiopian;;deposit;27-4504229;;333 Wickenden St., Providence, RI, 02903;
86;Wise Guys Deli;Jessica@corpgfm.com;deposit;;Wise Guys Deli, Inc;133 Atwells Avenue, Providence, RI, 02903;
82;Pho Paradise;;check;;Pho Paradise;337 Broad Street, Providence, RI, 02907;
99;Pizzoli's;;check;;Pizzoli's;1418 12th St NW, Washington, DC, 20005;
110;Sahara Mediterranean;colton.jang@yale.edu;check;;Scarabi, LLC Sahara Mediterinian Cuisine;170 Temple St, New Haven, CT, 06516;
106;Siam Best Thai Cuisine;;deposit;954889429;Siam Best Thai Cuisine;2533 Lincoln Blvd, Los Angeles, CA, 90291;
98;Boulder Salad;;check;;Boulder Salad;1310 College Ave, Boulder, CO, 80303;
103;Himalayan Cafe;;check;;Himalayan Cafe;36 S Fair Oaks Ave, Pasadena, CA, 91105;
94;Naraya Thai & Sushi;;deposit;273019414;;;
104;Numero Uno Pizza;;check;;Numero Uno Pizza;3562 E Foothill Blvd, Pasadena, CA, 91107;
111;Veggie Fun;;deposit;;Veggie Fun;123 Dorrance Street, Providence, RI, 02903;
115;Yoo Sushi;;check;;Yoo Sushi;412 Douglas Avenue, Providence, RI, 02908;
108;Leaf Organics;rod@leaforganics.com;deposit;27-2380052;;;
118;Marla's Cafe;;Check;;Marla's Cafe;2300 Abbot Kinney Blvd, Venice, CA, 90291;
120;Munch;;Check;;Munch Late Night;2300 Abbot Kinney Blvd, Venice, CA, 90291;
121;My Daddy's Grille;dbog96@aol.com;deposit;;My Daddy's Grille;;
122;San Marino Ristaurante;sanmarinosoho@gmail.com;Check;;San Marino SoHo;66 Charlton Street, New York, NY, 10013;
91;Milanos Pizzeria;;check;;Milanos Pizzeria;659 Smith Street, Providence, RI, 02908;
128;Li Li Wok;;check;;Li Li Wok;412 Douglas Avenue, Providence, RI, 02908;
85;Jordan's Hot Dogs & Mac;;check;;;957 State St., New Haven, CT, 06511;
133;La Paloma;;check;;;175 3rd Avenue, New York, NY, 10003;
134;The Wok;;check;;Longevity;180 Worcester Street, Wellesley, MA, 02481;
135;Big Tony's Pizza;;deposit;261123866;;;
123;Cilantro North Providence;;check;;;1650 Mineral Spring Avenue, North Providence, RI, 02904;
142;Little Thai Kitchen;;check;;;13 Popham Road, Scarsdale, NY, 10583;
143;Tomatillo Taco;tomatillo.taco@gmail.com;check;56626062001;316 Elm Street LLC;320 Elm St, New Haven, CT, 06511;
140;Weed World Candies;;deposit;;;;
146;Jeera Thai;dara_2525@hotmail.com;deposit;452784121;;;
154;Late Night Delivery;habchisaad9@aol.com;deposit;;Natalie's Pizzeria;;
170;Quiznos;;check;;;4317 Glencoe Avenue, Marina d. Rey, CA, 90292;
158;China Garden;hongchan68@hotmail.com;check;;;525 Washington Street, Brighton, MA, 02135;
156;Manny and Olgas;;check;;;1641 Wisconsin Ave NW, Washington, D.C., 20007;
173;Banana Delivery;;n/a;;;;
159;New York Pizza Depot;dtelemaco@comcast.net;deposit;383290836;;;
161;Pino's;;Check;;;1920-A Beacon Street, Brighton, MA, 02135;
185;Fresh in the box;;check;;;13354 W.  Washington Blvd, Los Angeles, CA, 90066;
184;Taj India Palace;tajindiapalace@yahoo.com;Check;;Gurbachan Inc.;8320 Lincoln Boulevard, Los Angeles, CA, 90045;
195;El Huarique;elhuariqueperu@hotmail.com;deposit;609-3831-56;;;
178;Trio House;ray@triohouse.com;Check;;;3031 S. Figueroa St., Los Angeles, CA, 90007;
176;Thai Corner Food Express;aritsaelliott@att.net;deposit;00022073300001-5;;;
168;China Express;;check;;;30 North Street, Burlington, VT, 05401;
164;Giovanni's Pizzeria;;check;;;12 East Park Row, Clinton, NY,13323;
179;Rio Grande Tex- Mex Grille;;check;;;3913 Oneida Street, New Hartford, NY, 13413;
165;Tony's Pizzeria;;check;;;41 College Street, Clinton, NY, 13323;
188;Uncle Tony's Pizza;;check;;;360 Dorset Street, S. Burlington, VT, 05403;
201;FoBoGro;;check;;FoBoGro - for Brianna DeBrock;2140 F St NW, Washington, DC, 20037;
196;Hannaford's;;n/a;;;;
197;Hill Party ;;n/a;;;;
181;Mitsuba;;n/a;;;17 Ellinwood Drive, New Hartford, NY, 13413;
179;Rio Grande Tex-Mex Grille;;check;;;3913 Oneida Street, New Hartford, NY, 13413;
167;Istanblue;;check;;;68 Congress Street, Saratoga Sprng, NY, 12866;
166;The Pizza 7;;check;;;7 Caroline Street, Saratoga Sprng, NY, 12866;
219;Subway;;n/a;;;;
218;Dunkin' Donuts;;n/a;;;;
213;Hill Tobacco;;n/a;;;;
198;Hill-Help;;n/a;;;;
180;McDonalds;;n/a;;;;
210;Benny's Tacos;bennystacos@aol.com;deposit;27-1743924;Meche LLC;521 Rose Ave, Venice, CA, 90291;
240;Five Guys ;;n/a;;;;
222;Panera Bread;;n/a;;;;
173;Free Banana Delivery ;;n/a;;;;
194;Pasta Roma;pastaromafig@sbcglobal.net;check;;KASGO LLC;2827 South Figueroa St., Los Angeles, CA, 90007;
244;Geo's Organic Coffee & Fine Tea;;check;;;4508 Inglewood Blvd, Culver City, CA, 90230;
241;Viztango;titorivera5@aol.com;check;;Tito Rivera;3017 S. Figueroa St., Los Angeles, CA, 90007;
193;Taste of the Islands;taste_of_the_islands@yahoo.com;check;;;3027 S. Hoover St., Los Angeles, CA, 90007;
220;Frankie & Johnnie's Pizza;nasserahdoot@gmail.com;check;;Nasser Ahdoot;534 W Washington Blvd, Marina del Rey, CA, 90292;
250;Free Crunch;;n/a;;;;
235;I am BC Gorilla;;n/a;;;;
248;G-DUB WINGZZZZ;;n/a;;;;
251;TG Express;;check;;New TG Express;1906 W 3rd St, Los Angeles, CA, 90057;
223;Mayura Amrit;;;Mayura Amrit LLC;1277 West Jefferson Boulevard, Los Angeles, CA, 90007;
256;Mayura Restaurant;;check;;Anjaneya Group Inc;10406 Venice Blvd, Culver City, CA, 90232;
254;Chipotle DELIVERED;;n/a;;;;
257;Ham-Mart;;n/a;;;;
258;Puppy Visit;;n/a;;;;
205;Lewiston House of Pizza;JKoutsikos@gmail.com;deposit;;Jimmy's House of Pizza;;
268;Morfia's Ribs and Pies;;check;;Morfia's Ribs and Pies;4077 Lincoln Boulevard, Marina del Rey, CA, 90292;
262;Pure Thai;pat_alen@hotmail.com;deposit;800788025;Pure Thai;65 College Street, Lewiston, ME, 04240;
225;Chopsticks;jenny@chopsticks-restaurant.com;deposit;01-0458548;Chang Inc.;;
274;Moes;;n/a;;;;
276;KFC;;n/a;;;;
277;Joe & Mimi's Pizzeria;joeandmimis@gmail.com;deposit;45-3279598;Rechovos LLC;;
297;22 Utica Street Cafe;;deposit;130548498;;;
281;Rusch's Bar & Grill;ruschsbarandgrill@gmail.com;deposit;392074739;;;
278;Chipotle;;n/a;;;;
279;Panera Bread;;n/a;;;;
280;7 Nana;;n/a;;;;
290;McDonalds;;n/a;;;;
291;Subway;;n/a;;;;
293;Dunkin Donuts;;n/a;;;;
302;McDonalds;;n/a;;;;
307;Subway;;n/a;;;;
308;Taco Bell;;n/a;;;;
309;Arby's;;n/a;;;;
310;Culver's;;n/a;;;;
311;Chipotle;;n/a;;;;
313;Al's Burger Shack;alsburgershack@gmail.com;deposit;462465914;AJEM Hospitality LLC;516 W. Franklin St., Chapel Hill, NC, 27516;
316;Artisan Pizza;artisanpizzach@gmail.com;deposit;263729508;;;
317;Sakura;sakuraxpress110@gmail.com;check;;Sakura Express;110 N Columbia St, Chapel Hill, NC, 27514;
318;McAlister's Deli;;check;;;205 E. Franklin Street, Chapel Hill, NC, 27514;
260;Royal Indian Grill;bhavjotchadha@gmail.com;deposit;452481282;SANT MANSARAMJI INC;6 Broad Street, Hamilton, NY, 13346;
261;Chipotle;;n/a;;;;
263;Panera Bread;;n/a;;;;
305;Pizza Hut;;n/a;;;;
319;Hamilton Eatery;;n/a;;;;
320;Drive for CB;;n/a;;;;
321;Drive for CB;;n/a;;;;
322;Drive for CB;;n/a;;;;
378;Wawa;;n/a;;;;
259;Chipotle;;n/a;;;;
264;Fresh to Order;;n/a;;;;
295;Sweetgreen;;n/a;;;;
380;In-N-Out;;n/a;;;;
383;BurgerFi;;n/a;;;;
385;Top This;pillyrivers@yahoo.com;check;;PM&R Corp;507 Gallberry Drive, Cary, NC, 27519;
389;Ken's Quick-E Mart;;deposit;464791219;;;
392;Chipotle;;n/a;;;;
395;Chipotle;;n/a;;;;
396;Chick-fil-A;;n/a;;;;
397;Panda Express;;n/a;;;;
324;Drive or Bike for us;;n/a;;;;
404;Chipotle;;n/a;;;;
386;Zoe's Kitchen;;n/a;;;;
403;Wawa;;n/a;;;;
325;We Need Delivery Bikers!;;n/a;;;;
327;We Need Drivers to Launch!;;n/a;;;;
406;Subway;;n/a;;;;
413;Spudnuts Donuts;;n/a;;;;
414;Ground Zero Performance Cafe;;n/a;;;;
415;Drive for CB;;n/a;;;;
416;Pizza Studio;;n/a;;;;
421;Bike for us!;;n/a;;;;
402;Fitzgerald's;sales@fitzgeraldschapelhill.com;check;;Milmar 206 LLC;206 West Franklin Street, Chapel Hill, NC, 27516;
408;New Buffet;;n/a;;;;
409;Panera Bread;;n/a;;;;
265;Ruben's Mexican Restaurant;;deposit;463435005;Josephine 2013 Corp;;
394;Healthy Fresh;;deposit;383895672;;;
410;Chipotle;;n/a;;;;
435;Raising Cane's;;n/a;;;;
447;McDonalds;;n/a;;;;
412;Uncle Darrow's;;Check;;Uncle Darrow's Incorporated;P.O. Box 35518, Los Angeles, CA, 90035;
446;Lime Fresh Mexican Grill;LF6330@limefmg.com;Check;;Lime Fresh Mexican Grill;140 West Franklin St. Suite 110, Chapel Hill, NC, 27516;
452;Hamilton Whole Foods;hamiltonwholefoodscb@gmail.com ;deposit;16-1391936;Hamilton Whole Foods LTD;;
453;VJ's;vjsrestaruant@gmail.com  ;deposit;20-5247904;Mangro's Italian Pizzeria Inc. ;;
461;Taco Bell;;n/a;;;;
467;old Top This;mike.itayem@yahoo.com;check;;Top This UNC LLC;2501 Blue Ridge Road G#190, Raleigh, NC, 27607;
411;Launch Crunchbutton Yourself;;n/a;;;;
422;Drive For Us Next Semester;;n/a;;;;
454;Drive for Crunchbutton;;n/a;;;;
460;Raising Cane's;;n/a;;;;
468;Red Onion;;deposit;600112782;Red Onion Café Inc.;;
465;Char;;check;;Char Restaurant;179 Howard Street, Boone, NC, 28607;
462;Parthenon;;deposit;75-3043417;Parthenon Café;;
463;California Kabob Kitchen;ckkrestaurant@gmail.com;Check;;Kabob Kitchen Inc;141 w 11th Street, Los Angeles, CA, 90015;
438;Food Haus;foodhauscafe@gmail.com;Check;;Food Haus Cafe;2106 S Olive St, Los Angeles, CA, 90007;
480;Klondike Cafe;KlondikeCafeBoone@yahoo.com;Check;;Klondike Cafe;441 Blowing Rock Rd, Boone, NC, 28607;
478;Cilantro's Mexican Grille;cilantros@live.com;Check;;Corrales Boone Inc.;783 West King Street, Boone, NC, 28607;
423;Opens at 6pm!;;n/a;;;;
470;drive for us;;n/a;;;;
488;old Benny's Tacos;bennystacos@aol.com;check;27-2143541;Benny's Tacos;427 Lincoln Blvd, Venice, CA, 90291;
469;Chipotle;;n/a;;;;
429;California Gogi Korean Grill;;n/a;;;;
479;Comeback Shack;;n/a;;;;
485;Budacki's;eve@budackishotdog.com;deposit;46-4018641;Dajin, Inc;;
492;Budacki's;eve@budackishotdog.com;deposit;46-4018641;Dajin, Inc;;
493;Salad Farm;;deposit;26-4360075;Sarara Enterprises Inc;;
444;Chichen Itza;gcetina@chichenitzarestaurant.com;deposit;20-5127671;Chichen Itza Food Service Inc.;;
498;Jack-In-The-Box;;n/a;;;;
500;McDonalds;;n/a;;;;
505;Drive for us!;;n/a;;;;
457;Cougar Country Drive In;;deposit;;Cougar Country Drive-In;;
474;Drive for Us;;n/a;;;;
512;In-N-Out;;n/a;;;;
513;McDonalds;;n/a;;;;
511;Chipotle;;n/a;;;;
497;Nature's Brew;naturesbreworders@gmail.com;deposit;;Nature's Brew Inc.;;
525;Taco Bell;;n/a;;;;
551;old Main Garden;;check;46-2716366;Jian Hua Zhu;376 Elm St, New Haven, CT, 06511;
509;Arby's;;n/a;;;;
515;Taco Bell;;n/a;;;;
533;Chipotle;;n/a;;;;
539;Penn Station East Coast Subs;;n/a;;;;
541;Buffalo Wild Wings;;n/a;;;;";
	}

}