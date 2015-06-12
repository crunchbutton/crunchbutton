<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {
	public function init() {

		$page = 1;
		$per_page = 50;
		$limit = ( ( $page - 1 ) * $per_page );
		$limit .= ',' . $per_page;

		$payments = Payment_Schedule::q( "select
DISTINCT( ps.id_payment ), ps.*
from order_transaction ot
inner join order_action oa on oa.id_order = ot.id_order
inner join payment_order_transaction pot on pot.id_order_transaction = ot.id_order_transaction
inner join payment_schedule ps on ps.id_payment = pot.id_payment
inner join admin_payment_type apt on apt.id_admin = oa.id_admin
where ot.type = 'paid-to-driver'
  and ps.pay_type = 'payment'
  and oa.type = 'delivery-accepted'
  and apt.payment_type = 'orders'
  and ps.date >= '2015-04-09'
  and ot.id_order in
    ( select distinct(o.id_order)
     from `order` o
     inner join order_action oa on oa.id_order = o.id_order
     inner join admin_payment_type apt on apt.id_admin = oa.id_admin
     inner join order_transaction ot on ot.id_order = o.id_order
     inner join payment_order_transaction pot on pot.id_order_transaction = ot.id_order_transaction
     inner join payment_schedule ps on ps.id_payment = pot.id_payment
     where ps.driver_payment_hours is null
       and o.date >= '2015-04-01'
       and o.refunded = 0
       and o.pay_type = 'cash'
       and o.delivery_service = 1
       and ot.type = 'paid-to-driver'
       and ot.amt is not null )
group by ps.id_payment_schedule LIMIT {$limit}" );


	$payments = Payment_Schedule::q( "select * from payment_schedule ps
inner join admin_payment_type apt on apt.id_admin = ps.id_driver

where

ps.driver_payment_hours is null

and ps.arbritary = 0

and ps.pay_type = 'payment'

and ps.date > '2015-04-09'

and ps.status = 'done'

order by ps.id_payment_schedule asc
LIMIT {$limit}" );



		$set = new Settlement();
		echo '<table border="1" rowspan="10">';

			echo '<tr>
							<th>id_payment_schedule</th>
							<th>id_driver</th>
							<th>driver name</th>
							<th>payment date</th>
							<th>should receive</th>
							<th>adjustment</th>
							<th>received</th>
							<th>invites amount</th>
							<th>fee collected</th>
							<th>missing</th>
							<th>url</th>
						</tr>';

		foreach ( $payments as $payment ) {

			$orders = $payment->orders();



			$summary = $set->driverSummary( $payment->id_payment_schedule );

			$ernings = $summary[ 'calcs' ][ 'earnings' ];
			$delivery_fee_collected = $summary[ 'calcs' ][ 'delivery_fee_collected' ];
			$adjustment = $payment->adjustment;
			$markup = $summary[ 'calcs' ][ 'markup' ];
			$delivery_fee = $summary[ 'calcs' ][ 'delivery_fee' ];
			$tip = $summary[ 'calcs' ][ 'tip' ];
			$markup = $summary[ 'calcs' ][ 'markup' ];
			$invites_amount = $summary[ 'invites_amount' ];

			$should_receive = number_format( ( $delivery_fee + $tip + ( $delivery_fee_collected * -1 ) + $markup ) + $adjustment + $invites_amount, 2 );

			$missing = $should_receive - $payment->amount;

			if( intval( $missing ) != intval( ( $delivery_fee_collected * -1 ) ) ){
				$color = 'red';
			} else {
				$color = 'blue';
			}

			echo '<tr style="color:' . $color . ';">';

				echo '<td>';
					echo $payment->id_payment_schedule;
				echo '</td>';

				echo '<td>';
					echo $payment->id_driver;
				echo '</td>';

				echo '<td>';
					echo $summary[ 'driver' ];
				echo '</td>';

				echo '<td>';
					echo $summary[ 'date' ];
				echo '</td>';

				echo '<td>';
					echo $should_receive;
				echo '</td>';

				echo '<td>';
					echo ( $adjustment ? $adjustment : 0 );
				echo '</td>';

				echo '<td>';
					echo ( $payment->amount ? $payment->amount : 0 );
				echo '</td>';

				echo '<td>';
					echo ( $invites_amount ? $invites_amount : 0 );
				echo '</td>';

				echo '<td>';
					echo $delivery_fee_collected;
				echo '</td>';

				echo '<td>';
					echo $missing;
				echo '</td>';

					$url = 'http://dev.la/settlement/drivers/scheduled/' . $payment->id_payment_schedule;

				echo '<td>';
					echo '<a href="' . $url . '">' . $url . '</a>';
				echo '</td>';



			echo '</tr>';
		}
		echo '</table>';

	}
}