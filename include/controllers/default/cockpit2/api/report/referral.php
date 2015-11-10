<?php

class Controller_Api_Report_Referral extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( [ 'global' ] ) ){
			$this->_error();
		}

		header( 'Content-Type: text/html' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		// REFERRALS
		$query = "SELECT r.*,
										 psr.id_payment_schedule_referral,
										 ps.id_payment_schedule,
										 p.id_payment,
										 psr.amount,
										 p.payment_status,
										 DATE_FORMAT(p.date, '%b') AS Month
							FROM referral r
							INNER JOIN payment_schedule_referral psr ON psr.id_referral = r.id_referral
							INNER JOIN payment_schedule ps ON ps.id_payment_schedule = psr.id_payment_schedule
							INNER JOIN payment p ON p.id_payment = ps.id_payment
							WHERE year(r.date) = 2015
								AND r.new_user = 1
								AND id_admin_inviter IS NOT NULL";

		$r = c::db()->query( $query, $keys );

		$out = [ 	'Driver' => [],
							'Brand Representative' => [],
							'Community Manager' => [],
							'Other' => [],
							'Customer*' => [],
							'Total' => [], ];

		while ( $referral = $r->fetch()) {
			$out[ 'Total' ][ $referral->Month ] += $referral->amount;
			$staff = Admin::o( $referral->id_admin_inviter );
			if( $staff->isCampusManager() ){
				$out[ 'Community Manager' ][ $referral->Month ] += $referral->amount;
			} else
			if( $staff->isMarketingRep() ){
				$out[ 'Brand Representative' ][ $referral->Month ] += $referral->amount;
			} else
			if( $staff->isDriver() ){
				$out[ 'Driver' ][ $referral->Month ] += $referral->amount;
			} else {
				$out[ 'Other' ][ $referral->Month ] += $referral->amount;
			}
		}

		$query = "SELECT *, DATE_FORMAT(c.date, '%b') AS Month FROM credit c WHERE c.id_referral IS NOT NULL AND year(c.date) = 2015 AND c.credit_type = 'cash' AND type = 'CREDIT'";

		$r = c::db()->query( $query, $keys );
		while ( $referral = $r->fetch()) {
			$out[ 'Total' ][ $referral->Month ] += $referral->value;
			$out[ 'Customer*' ][ $referral->Month ] += $referral->value;
		}

		echo "<h1>2015</h1>";

		echo "<h2>Referral (US$)</h2>";
		echo $this->tablefy( $out );
		echo "*Customer: credits";
		echo "<hr/>";

		// GIFT CARDS
		$out = [];
		$query = "SELECT *, DATE_FORMAT(c.date, '%b') AS Month FROM credit c WHERE c.id_promo IS NOT NULL AND year(c.date) = 2015 AND c.credit_type = 'cash' AND type = 'CREDIT'";

		$r = c::db()->query( $query, $keys );
		while ( $referral = $r->fetch()) {
			$out[ 'Amount' ][ $referral->Month ] += $referral->value;
		}

		echo "<h2>Gift Cards Redeemed (US$)</h2>";
		echo $this->tablefy( $out );

		// DELIVERY FREE
		$out = [];
		$query = "SELECT *, DATE_FORMAT(c.date, '%b') AS Month FROM credit c WHERE year(c.date) = 2015 AND c.credit_type = 'cash' AND type = 'CREDIT' AND note = 'Reward: delivery free'";

		$r = c::db()->query( $query, $keys );
		while ( $referral = $r->fetch()) {
			$out[ 'Amount' ][ $referral->Month ] += $referral->value;
		}

		echo "<h2>Delivery Free by Points (US$)</h2>";
		echo $this->tablefy( $out );

	}

	public function tablefy( $data ){
		echo "<table border=1>";
			echo "<tr>";
			echo "<th>Month</th>";
			foreach( $data AS $key => $val ){
				echo "<th>{$key}</th>";
			}
			echo "</tr>";
			$months = [ 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec' ];
			foreach( $months AS $month ){
				echo "<tr>";
					echo "<th>{$month}</th>";
					foreach( $data AS $key => $val ){
						echo "<td>{$val[$month]}</td>";
					}
				echo "</tr>";
			}
		echo "</table>";
	}

}
