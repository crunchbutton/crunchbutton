#!/usr/bin/env php
<?php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);
set_time_limit(0);

require_once('../include/crunchbutton.php');

// how many times to run
$run = 10;

$dbs = [
	'local' => [
		'user' => 'root',
		'pass' => 'root',
		'database' => 'crunchbutton',
		'host' => '127.0.0.1',
		'details' => [
			'size' => 0,
			'host' => '',
			'price' => 0
		]
	],
	'linode' => [
		'user' => c::crypt()->decrypt(c::config()->db->live->user),
		'pass' => c::crypt()->decrypt(c::config()->db->live->pass),
		'database' => 'crunchbutton',
		'host' => 'db3._DOMAIN_',
		'details' => [
			'size' => '8192',
			'host' => 'linode',
			'price' => 100
		]
	],
	'auora' => [
		'user' => c::crypt()->decrypt(c::config()->db->live->user),
		'pass' => c::crypt()->decrypt(c::config()->db->live->pass),
		'database' => 'crunchbutton',
		'host' => '_HOST_',
		'details' => [
			'size' => 'r3.large',
			'host' => 'rds',
			'price' => 126
		]
	],
	'mariadb' => [
		'user' => c::crypt()->decrypt(c::config()->db->live->user),
		'pass' => c::crypt()->decrypt(c::config()->db->live->pass),
		'database' => 'crunchbutton',
		'host' => '_HOST_',
		'details' => [
			'size' => 'r3.large',
			'host' => 'rds',
			'price' => 126
		]
	],
	'mysql' => [
		'user' => c::crypt()->decrypt(c::config()->db->live->user),
		'pass' => c::crypt()->decrypt(c::config()->db->live->pass),
		'database' => 'crunchbutton',
		'host' => 'crunchbutton-va-mysql.cstvhlbo53mv.us-east-1.rds.amazonaws.com',
		'details' => [
			'size' => 'r3.large',
			'host' => 'rds',
			'price' => 126
		]
	]
];

if ($argv[1]) {
	$dbs = [$argv[1] => $dbs[$argv[1]]];
}


$queries = [
	'order-count' => 'select count(*) from `order`',
	'active-users' => 'SELECT \'201526\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201526 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201526 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201527\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201527 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201527 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201528\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201528 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201528 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201529\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201529 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201529 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201530\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201530 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201530 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201531\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201531 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201531 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201532\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201532 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201532 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201533\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201533 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201533 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201534\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201534 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201534 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201535\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201535 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201535 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201536\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201536 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201536 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201537\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201537 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201537 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201538\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201538 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201538 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201539\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201539 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201539 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201540\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201540 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201540 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201544\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201544 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201544 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers UNION SELECT \'201546\' AS Week, COUNT(*) AS Total FROM ( SELECT o.phone, o.date, o.id_user FROM `order` o WHERE o.date <= STR_TO_DATE(\'201546 Saturday\', \'%X%V %W\') AND o.date >= STR_TO_DATE(\'201546 Saturday\', \'%X%V %W\') - INTERVAL 45 DAY AND o.name NOT LIKE \'%test%\' and o.name != \'Judd\' and o.name != \'dave\' and o.name != \'Nick\' and o.name != \'Devin\' GROUP BY o.phone) ActiveUsers',
	'restaurants-usc' => '
			SELECT
				count(*) as _weight,
				CAST(restaurant.loc_lat as DECIMAL(19,15)) as loc_lat,
				CAST(restaurant.loc_long as DECIMAL(19,15)) as loc_long,
				\'byrange\' as type,
				( ( ACOS( SIN( 34.023281 * PI() / 180 ) * SIN( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) + COS( 34.023281 * PI() / 180 ) * COS( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -118.288196 - CAST(loc_long as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) AS distance,
				restaurant.*
			FROM restaurant
				LEFT JOIN `order` o ON o.id_restaurant = restaurant.id_restaurant AND o.date > \'2015-10-20 00:00:00\'
				WHERE
					active = true AND delivery_radius_type = \'restaurant\'
				GROUP BY restaurant.id_restaurant
				HAVING
						takeout = true
					AND
						delivery = false
					AND
						( ( ACOS( SIN( 34.023281 * PI() / 180 ) * SIN( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) + COS( 34.023281 * PI() / 180 ) * COS( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -118.288196 - CAST(loc_long as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= 2
					OR
						delivery = true
					AND
						( ( ACOS( SIN( 34.023281 * PI() / 180 ) * SIN( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) + COS( 34.023281 * PI() / 180 ) * COS( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -118.288196 - CAST(loc_long as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= (delivery_radius + 0 )  UNION
  			SELECT
  				count(*) as _weight,
  				CAST(max(c.loc_lat) as DECIMAL(19,15)) AS loc_lat,
  				CAST(max(c.loc_lon) as DECIMAL(19,15)) AS loc_long,
  				\'byrange\' as type,
  				( ( ACOS( SIN( 34.023281 * PI() / 180 ) * SIN( CAST(max(r.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) + COS( 34.023281 * PI() / 180 ) * COS( CAST(max(r.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -118.288196 - CAST(max(r.loc_long) as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) AS distance,
  				r.*
  			FROM restaurant r
  			LEFT JOIN `order` o ON o.id_restaurant = r.id_restaurant AND o.date > \'2015-10-20 00:00:00\'
  			INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant
  			INNER JOIN community c ON c.id_community = rc.id_community
  			WHERE
  				r.active = true AND r.delivery_radius_type = \'community\' AND c.active = true
  			GROUP BY r.id_restaurant
  			 HAVING
  					r.takeout = true
  				AND
  					r.delivery = false
  				AND
  					( ( ACOS( SIN( 34.023281 * PI() / 180 ) * SIN( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) + COS( 34.023281 * PI() / 180 ) * COS( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -118.288196 - CAST(max(c.loc_lon) as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= 2
  				OR
  					delivery = true
  				AND
  					( ( ACOS( SIN( 34.023281 * PI() / 180 ) * SIN( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) + COS( 34.023281 * PI() / 180 ) * COS( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -118.288196 - CAST(max(c.loc_lon) as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= (delivery_radius + 0 )  ORDER BY _weight DESC; ',
	'restaurants-test' => '
			SELECT
				count(*) as _weight,
				CAST(restaurant.loc_lat as DECIMAL(19,15)) as loc_lat,
				CAST(restaurant.loc_long as DECIMAL(19,15)) as loc_long,
				\'byrange\' as type,
				( ( ACOS( SIN( 33.175101 * PI() / 180 ) * SIN( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) + COS( 33.175101 * PI() / 180 ) * COS( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -96.677810 - CAST(loc_long as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) AS distance,
				restaurant.*
			FROM restaurant
				LEFT JOIN `order` o ON o.id_restaurant = restaurant.id_restaurant AND o.date > \'2015-10-20 00:00:00\'
				WHERE
					active = true AND delivery_radius_type = \'restaurant\'
				GROUP BY restaurant.id_restaurant
				HAVING
						takeout = true
					AND
						delivery = false
					AND
						( ( ACOS( SIN( 33.175101 * PI() / 180 ) * SIN( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) + COS( 33.175101 * PI() / 180 ) * COS( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -96.677810 - CAST(loc_long as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= 2
					OR
						delivery = true
					AND
						( ( ACOS( SIN( 33.175101 * PI() / 180 ) * SIN( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) + COS( 33.175101 * PI() / 180 ) * COS( CAST(loc_lat as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -96.677810 - CAST(loc_long as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= (delivery_radius + 0 )  UNION
  			SELECT
  				count(*) as _weight,
  				CAST(max(c.loc_lat) as DECIMAL(19,15)) AS loc_lat,
  				CAST(max(c.loc_lon) as DECIMAL(19,15)) AS loc_long,
  				\'byrange\' as type,
  				( ( ACOS( SIN( 33.175101 * PI() / 180 ) * SIN( CAST(max(r.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) + COS( 33.175101 * PI() / 180 ) * COS( CAST(max(r.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -96.677810 - CAST(max(r.loc_long) as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) AS distance,
  				r.*
  			FROM restaurant r
  			LEFT JOIN `order` o ON o.id_restaurant = r.id_restaurant AND o.date > \'2015-10-20 00:00:00\'
  			INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant
  			INNER JOIN community c ON c.id_community = rc.id_community
  			WHERE
  				r.active = true AND r.delivery_radius_type = \'community\' AND c.active = true
  			GROUP BY r.id_restaurant
  			 HAVING
  					r.takeout = true
  				AND
  					r.delivery = false
  				AND
  					( ( ACOS( SIN( 33.175101 * PI() / 180 ) * SIN( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) + COS( 33.175101 * PI() / 180 ) * COS( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -96.677810 - CAST(max(c.loc_lon) as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= 2
  				OR
  					delivery = true
  				AND
  					( ( ACOS( SIN( 33.175101 * PI() / 180 ) * SIN( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) + COS( 33.175101 * PI() / 180 ) * COS( CAST(max(c.loc_lat) as DECIMAL(19,15)) * PI() / 180 ) * COS( ( -96.677810 - CAST(max(c.loc_lon) as DECIMAL(19,15)) ) * PI() / 180 ) ) * 180 / PI() ) * 60 * 1.1515 ) <= (delivery_radius + 0 )  ORDER BY _weight DESC; ',
	'cockpit-orders-search' => '
			SELECT

			`order`.*,
			max(restaurant.name) as _restaurant_name,
			max(restaurant.phone) as _restaurant_phone,
			max(restaurant.permalink) as _restaurant_permalink,
			bool_and(restaurant.confirmation) as _restaurant_confirmation,
			max(community.name )as _community_name,
			max(community.permalink) as _community_permalink,
			max(community.id_community) as _community_id,
			max(admin.name) as _driver_name,
			max(admin.id_admin) as _driver_id,
			restaurant.formal_relationship

			FROM `order`
			left JOIN restaurant ON restaurant.id_restaurant=`order`.id_restaurant
			left JOIN restaurant_community ON restaurant_community.id_restaurant=restaurant.id_restaurant
			left JOIN community ON community.id_community=restaurant_community.id_community

			LEFT JOIN order_action ON order_action.id_order=`order`.id_order
			LEFT JOIN admin ON admin.id_admin=order_action.id_admin
			LEFT JOIN phone ON phone.id_phone=`order`.id_phone


			WHERE `order`.id_restaurant IS NOT NULL

			GROUP BY `order`.id_order
			ORDER BY `order`.id_order DESC

			LIMIT 100
		',
	'cockpit-staff-search' => '
			SELECT
						admin.*,
						bool_and(apt.using_pex) using_pex,
						max(apt.id_admin_payment_type) id_admin_payment_type
					 FROM admin

			INNER JOIN admin_payment_type apt ON apt.id_admin = admin.id_admin
			INNER JOIN phone p ON admin.id_phone = p.id_phone

			WHERE 1=1

				AND admin.active=?

				AND (( admin.name  LIKE "%devin%"
 OR  admin.phone  LIKE "%devin%"
 OR  admin.login  LIKE "%devin%"
 OR  admin.email  LIKE "%devin%"
))

			GROUP BY `admin`.id_admin

			ORDER BY `admin`.name ASC

				LIMIT 100
			'

];


foreach ($dbs as $k => $db) {

	$pdo = new PDO(
		'mysql:host='.$db['host'].';dbname='.$db['database'],
		$db['user'],
		$db['pass']
	);
	$pdo->query('set profiling=1');
	$pdo->query('set profiling_history_size='.count($queries));

	$res = [];
	for ($x=1; $x <= $run; $x++) {

		foreach ($queries as $query) {
			echo '.';
			$pdo->query($query);
		}

		$stmt = $pdo->query('show profiles');
		$records = $stmt->fetchAll(PDO::FETCH_OBJ);


		$i = 0;
		foreach ($queries as $kk => $query) {
			$res[$kk][] = $records[$i]->Duration;
			$i++;
		}
	}


	foreach ($queries as $kk => $query) {
		$ress[$k][$kk]['repeat'] = number_format(array_sum($res[$kk]) / count($res), 7);
		$ress[$k][$kk]['single'] = number_format($res[$kk][0],7 );
	}

	$pdo = null;
}

print_r($ress);

foreach ($dbs as $db) {
	foreach ($db['details'] as $k => $v)
	$fileds[$k] = true;
}

$out = "DB,runs";
foreach ($ress as $r) {
	foreach ($fileds as $field) {
		$out .= ','.$field;
	}
	foreach ($r as $k => $q) {
		$out .= ','.$k;
	}
	$out .= ",average\n";
	break;
}

foreach ($ress as $name => $r) {
	$out .= $name.',1';
	foreach ($fileds as $field => $n) {
		$out .= ','.$dbs[$name]['details'][$field];
	}
	$t = 0;
	foreach ($r as $q) {
		$out .= ','.$q['single'];
		$t += $q['single'];
	}
	$out .= ','.number_format($t / count($r), 7);
	$out .= "\n";
}

foreach ($ress as $name => $r) {
	$out .= $name.','.$run;
	foreach ($fileds as $field => $n) {
		$out .= ','.$dbs[$name]['details'][$field];
	}
	$t = 0;
	foreach ($r as $q) {
		$out .= ','.$q['repeat'];
		$t += $q['repeat'];
	}
	$out .= ','.number_format($t / count($r), 7);
	$out .= "\n";
}

file_put_contents('db-test.csv', $out);

echo "\n\nOutput written to db-test.csv\n";
