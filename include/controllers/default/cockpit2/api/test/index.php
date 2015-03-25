<?php

class Controller_api_test extends Cana_Controller {
	public function init(){

	die('hard');
		$community = Crunchbutton_Community::o( 197 );

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		// 77126

		$date = '2015-03-18 18:00:00';

		echo '<pre>';var_dump( $community->shutDownCommunity( $date ) );exit();
echo '<pre>';var_dump( 1 );exit();
		die('hard');

		// $admin = Admin::o( 5 );
		// echo '<pre>';var_dump( $admin->getLastWorkedTimeHours() );exit();


		$community = Community::o( 6 );
		$community->shutDownCommunity( $dt );
		die('hard');


		$query = "DATE_SUB( NOW(), INTERVAL '12 HOUR' ) ";

		$query = preg_replace('/(date_sub\((.*?),(.*?))\)/i','\\2 - \\3', $query);
		echo $query;
		exit;

		$qs = [
			" (DATE_FORMAT( date_start, '%Y-%m-%d' ) <= 2012-12-12 AND DATE_FORMAT( date_start, '%Y-%m-%d' ) <= 1015-01-01) ",
			"( DATE_FORMAT( date_start, '%Y-%m-%d' )",
			"DATE_FORMAT( date_start, '%Y-%m-%d')",
			"DATE_FORMAT(date_start, '%Y-%m-%d')",
			'DATE_FORMAT(date_start, "%Y-%m-%d")',
			'DATE_FORMAT(date_start,"%Y-%m-%d")',
			'DATE_FORMAT(date_start,"%Y-%m-%d")',
			'DATE_FORMAT(date_start,"%Y%m%d")',
			" AND DATE_FORMAT( date_start, '%Y-%m-%d' ) <= "
		];
		foreach ($qs as $query) {
			$query = preg_replace_callback('/date_format\(( )?(.*?),( )?("(.*?)"|\'(.*?)\')( )?\)/i',function($m) {
				print_r($m);
				$find = ['/\%Y/', '/\%m/', '/\%d/', '/\%H/', '/\%i/', '/\%s/', '/\%W/'];
				$replace = ['YYYY', 'MM', 'DD', 'HH24', 'MI', 'SS', 'D'];
				$format = preg_replace($find, $replace, $m[6] ? $m[6] : $m[5]);
				return 'to_char('.$m[2].', \''.$format.'\')';
			}, $query);
			echo $query."\n";
		}
		exit;


		// echo '<pre>';var_dump( $_REQUEST[ 'cockpit' ], $_SERVER['HTTP_HOST'] );exit();
		// test
		// $agent = Crunchbutton_Agent::getAgent();


		// $dt = '2015-02-20 08:02:03';

		// $restaurant = Restaurant::o( 828 );
		// echo '<pre>';var_dump( $restaurant->open( $dt ) );exit();

		// $community = Community::o( 70 );

		// echo '<pre>';var_dump( $community->forceCloseLog() );exit();;
		// $community->reopenAutoClosedCommunity( $dt );
		// Community::shutDownCommunities();

	}
}