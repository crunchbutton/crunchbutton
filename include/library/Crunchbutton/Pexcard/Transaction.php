<?php

class Crunchbutton_Pexcard_Transaction extends Crunchbutton_Pexcard_Resource {

	public function transactions( $start, $end ){
		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {
			case 'v4':
				return Crunchbutton_Pexcard_Transaction::allCardHolderTransactions( $start, $end );
				break;
			case 'v3':
				$transactions = Crunchbutton_Pexcard_Resource::request( 'spendbytransactionreport', [ 'StartTime' => $start, 'EndTime' => $end ] );
				if( $transactions->body ){
					return $transactions->body->transactions;
				}
				else if( $transactions->message ){
					return $transactions->message;
				} else {
					return false;
				}
				break;
		}
	}

	public function allCardHolderTransactions( $start, $end ){

		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {

			case 'v4':
				$start = explode( '/' , $start );
				$start = $start[2] . '-' . $start[0] . '-' . $start[1] . 'T00:00:01';
				$end = explode( '/' , $end );
				$end = $end[2] . '-' . $end[0] . '-' . $end[1] . 'T23:59:59';

				$params = [ 'StartDate' => $start, 'EndDate' => $end, 'IncludePendings' => 'true' ];
				$transactions = Crunchbutton_Pexcard_Resource::request( 'allcardholdertransactions', $params );

				if( $transactions->body && $transactions->body->TransactionList ){

					$_transactions = [];
					$transactions = $transactions->body->TransactionList;
					foreach( $transactions as $transaction ){

						$pexcard = Cockpit_Admin_Pexcard::getByPexcard( $transaction->AcctId );

						if( $pexcard->id_admin_pexcard ){
							$serial = $pexcard->card_serial;
							$last_four = $pexcard->last_four;
						} else {
							$serial = null;
							$last_four = null;
						}

						$_transactions[] = ( object ) [ 'id' => $transaction->TransactionId,
																						'acctId' => $transaction->AcctId,
																						'transactionTime' => $transaction->TransactionTime,
																						'settlementTime' => $transaction->SettlementTime,

																						'transactionCode' => null,

																						'firstName' => 'Crunchbutton',
																						'middleName' => null,
																						'lastName' => $serial,
																						'cardNumber' => $last_four,

																						'spendCategory' => null,
																						'description' => $transaction->Description,
																						'amount' => $transaction->TransactionAmount,

																						'transferToOrFromAccountId' => $transaction->TransferToOrFromAccountId,
																						'transactionType' => $transaction->TransactionType,
																						'isPending' => $transaction->IsPending,
																						'isDecline' => $transaction->IsDecline,
																						'transactionNotes' => $transaction->TransactionNotes,
																						'paddingAmount' => $transaction->paddingAmount,
																						'merchantName' => $transaction->MerchantName,
																						'merchantCity' => $transaction->MerchantCity,
																						'merchantState' => $transaction->MerchantState,
																						'merchantCountry' => $transaction->MerchantCountry,
																						'MCCCode' => $transaction->MCCCode,
																						'authTransactionId' => $transaction->AuthTransactionId,
																					];
					}
					return $_transactions;
				} if( $transactions->message ){
					return $transactions->message;
				} else {
					return false;
				}
				break;
		}
	}

	public function loadTransactionDetails( $id, $start, $end ){
		return Crunchbutton_Pexcard_Resource::request( 'transactiondetails', [ 'id' => $id, 'StartTime' => $start, 'EndTime' => $end  ] );
	}

	public function getByTransactionId( $transactionId ){
		return Crunchbutton_Pexcard_Transaction::q( 'SELECT * FROM pexcard_transaction WHERE transactionId = ?', [$transactionId])->get( 0 );
	}

	public function loadTransactions(){
		ini_set( 'memory_limit', '-1' );
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$end = $now->format( 'm/d/Y' );
		$now->modify( '-4 days' );
		$start = $now->format( 'm/d/Y' );
		Crunchbutton_Pexcard_Transaction::saveTransactionsByPeriod( $start, $end );
	}

	public function convertTimeZone(){
		$transactions = Crunchbutton_Pexcard_Transaction::q( 'SELECT * FROM pexcard_transaction WHERE transactionTime_pst IS NULL ORDER BY id_pexcard_transaction DESC LIMIT 10000' );
		foreach( $transactions as $transaction ){
			$date = new DateTime( $transaction->transactionTime, new DateTimeZone( 'America/Chicago' ) );
			$date->setTimezone( new DateTimeZone( c::config()->timezone ) );
			$transaction->transactionTime_pst = $date->format( 'Y-m-d H:i:s' );
			$transaction->save();
		}
	}

	public function saveTransactionsByPeriod( $start, $end ){
		$start = new DateTime( $start );
		$end = new DateTime( $end );
		if( $start->format( 'm/d/Y' ) == $end->format( 'm/d/Y' ) ){
			$start->modify( '-1 day' );
		}
		$transactions = Crunchbutton_Pexcard_Transaction::transactions( $start->format( 'm/d/Y' ), $end->format( 'm/d/Y' ) );
		foreach( $transactions as $transaction ){
			Crunchbutton_Pexcard_Transaction::saveTransaction( $transaction );
		}
		return true;
	}

	public function getOrderExpenses( $start, $end, $only_card = true ){
		$where = ( $only_card ) ? 'AND o.pay_type = "' . Crunchbutton_Order::PAY_TYPE_CREDIT_CARD . '"' : '';
		$query = 'SELECT SUM( o.final_price - o.delivery_fee ) amount,
									a.id_admin,
									a.login,
									COUNT(1) AS orders,
									a.name AS driver,
									a.email
									FROM `order` o
											INNER JOIN order_action oa ON o.id_order = oa.id_order
											INNER JOIN admin_payment_type apt ON apt.id_admin = oa.id_admin
											INNER JOIN admin a ON oa.id_admin = a.id_admin
											INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.formal_relationship = false
											WHERE
												apt.using_pex = true
												AND o.refunded = 0
											AND
												oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '"
											' . $where . '
											AND
												o.date >= "' . $start . '"
											AND
												o.date <= "' . $end . '"
											GROUP BY a.id_admin ORDER BY driver' ;
		$expenses = c::db()->get( $query );
		return $expenses;
	}

	public function getExpensesByPeriod( $start, $end ){
		$query = 'SELECT acctId
								FROM pexcard_transaction
									WHERE
										transactionTime_pst BETWEEN "' . $start . '" AND "' . $end . '"
										AND transactionType != "Transfer"
								GROUP BY acctId
								ORDER BY amount DESC';
		$expenses = c::db()->get( $query );
		return $expenses;
	}

		public function getExpensesByPeriodByCard( $start, $end, $acctId ){
		$query = 'SELECT *
								FROM pexcard_transaction
									WHERE
										transactionTime_pst BETWEEN ? AND ?
										AND transactionType != "Transfer"
										AND acctId = ?
								ORDER BY transactionTime ASC, isPending DESC';
		$expenses = c::db()->get( $query, [$start, $end, $acctId]);
		return $expenses;
	}

	public function getOrderExpensesByDriver( $start, $end, $id_admin ){
		$query = 'SELECT
									DISTINCT(o.id_order),
									( o.final_price - o.delivery_fee ) amount,
									r.name as restaurant,
									o.*
									FROM `order` o
											INNER JOIN order_action oa ON o.id_order = oa.id_order
											INNER JOIN admin_payment_type apt ON apt.id_admin = oa.id_admin
											INNER JOIN admin a ON oa.id_admin = a.id_admin
											INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.formal_relationship = false
											WHERE
												apt.using_pex = true
											AND
												oa.type = ?
											AND
											o.date BETWEEN ? AND ?
											AND a.id_admin = ?
											ORDER BY o.pay_type DESC, o.date ASC';
		return Crunchbutton_Order::q( $query, [Crunchbutton_Order_Action::DELIVERY_ACCEPTED, $start, $end, $id_admin]);
	}

	public function processExpenses( $start, $end ){

		$sameDay = false;
		if( $start == $end ){
			$sameDay = true;
		}

		$start = explode( '/' , $start );
		$start = new DateTime( $start[ 2 ] . '-' . $start[ 0 ] . '-' . $start[ 1 ] . ' 00:00:01', new DateTimeZone( c::config()->timezone ) );
		$start->modify( '+4 hours' );
		$pst_start = $start->format( 'Y-m-d H:i' );

		$start->setTimezone( new DateTimeZone( 'America/Chicago' ) );
		$est_start = $start->format( 'Y-m-d H:i' );

		$end = explode( '/' , $end );
		$end = new DateTime( $end[ 2 ] . '-' . $end[ 0 ] . '-' . $end[ 1 ] . ' 23:59:59', new DateTimeZone( c::config()->timezone ) );

		$end->modify( '+4 hours' );

		$pst_end = $end->format( 'Y-m-d H:i' );

		$end->setTimezone( new DateTimeZone( 'America/Chicago' ) );
		$est_end = $end->format( 'Y-m-d H:i' );

		$pex_expenses = Crunchbutton_Pexcard_Transaction::getExpensesByPeriod( $pst_start, $pst_end );

		$cards = [];
		$report = [];

		foreach( $pex_expenses as $card ){

			$pexcard = Cockpit_Admin_Pexcard::getByPexcard( $card->acctId );

			$admin = $pexcard->admin();

			$info = [ 'id_pexcard' => intval( $card->acctId ) ];
			$info[ 'card_serial' ] = intval( $pexcard->card_serial );
			$info[ 'last_four' ] = $pexcard->last_four;
			$info[ 'id_admin' ] = $pexcard->id_admin;
			$info[ 'driver' ] = $admin->name;
			$info[ 'login' ] = $admin->login;
			$info[ 'email' ] = $admin->email;
			$info[ 'cash_orders' ] = false;
			$info[ 'card_orders' ] = false;
			$info[ 'pexcard_amount' ] = 0;
			$info[ 'card_cash_amount' ] = 0;
			$info[ 'card_amount' ] = 0;

			$info[ 'show_transactions' ] = false;
			$info[ 'transactions' ] = [];

			$admin = $pexcard->admin();
			if( $admin->timezone ){
				$timezone = $admin->timezone;
			} else {
				$timezone = c::config()->timezone;
			}

			$transactions = Crunchbutton_Pexcard_Transaction::getExpensesByPeriodByCard( $pst_start, $pst_end, $card->acctId );

			$transactions_already_added = [];

			foreach( $transactions as $transaction ){

				if( !$transactions_already_added[ $transaction->authTransactionId ] ){
					$date = new DateTime( $transaction->transactionTime, new DateTimeZone( c::config()->timezone ) );
					$info[ 'transactions' ][] = [ 'date' => $date->format( 'M jS Y g:i:s A T' ),
																		'description' => $transaction->merchantName,
																		'amount' => $transaction->amount ];
					$info[ 'pexcard_amount' ] += number_format( $transaction->amount, 2 );
				}



				$transactions_already_added[ $transaction->authTransactionId ] = true;

			}

			$info[ 'delivered_orders' ] = [];
			$total_amount_orders = 0;
			$orders = Crunchbutton_Pexcard_Transaction::getOrderExpensesByDriver( $pst_start, $pst_end, $info[ 'id_admin' ] );
			foreach( $orders as $order ){
				$get_out = true;
				$last = $order->status()->last();

				if( $last[ 'status' ] == 'delivered' && $last[ 'driver' ] && $last[ 'driver' ][ 'id_admin' ] && $last[ 'driver' ][ 'id_admin' ] == $info[ 'id_admin' ] ){
					$get_out = false;
				}

				if( $last[ 'status' ] == 'canceled' && Crunchbutton_Order_Action::orderWasPickedUp( $order->id_order, $info[ 'id_admin' ] ) ){
					// check if the order was picked up
					$get_out = false;
				}

				if( $get_out ){
					continue;
				}

				$info[ 'card_cash_amount' ] += number_format( $order->amount, 2 );
				if( $order->pay_type == 'card' ){
					$info[ 'card_amount' ] += number_format( $order->amount, 2 );
					$info[ 'card_orders' ] = true;
				} else {
					$info[ 'cash_orders' ] = true;
				}

				$date = $order->date();
				$total_amount_orders += number_format( $order->amount, 2 );
				$info[ 'delivered_orders' ][] = [ 'date' => $date->format( 'M jS Y g:i:s A T' ),
																					'restaurant' => $order->restaurant,
																					'pay_type' => $order->pay_type,
																					'id_order' => $order->id_order,
																					'refunded' => $order->refunded,
																					'status' => $last[ 'status' ],
																					'amount' => number_format( $order->amount, 2 ) ];
			}

			$cards[] = $info;

		}

		foreach( $cards as $card ){

			if( !intval( $card[ 'id_admin' ] ) ){

				$card = array_merge( $card, [ 'driver' => 'Card not assigned',
																			'card_amount' => 0,
																			'email' => '',
																			'diff' => 0,
																			'sort' => ( 'zz' . $card[ 'last_four' ] ),
																			'card_cash_amount' => 0 ] );
				$report[] = $card;
				continue;
			}

			if( $card[ 'id_admin' ] ){
				$card[ 'sort' ] = $card[ 'driver' ];
				$card[ 'sort' ] = $card[ 'driver' ];
				$card[ 'diff' ] = ( number_format( $card[ 'pexcard_amount' ] - $card[ 'card_amount' ], 2 ) ) * -1;
				$card[ 'orders' ] = count( $card[ 'delivered_orders' ] );
			}


			if( !$card[ 'driver' ] ){

				$admin = Admin::o( $card[ 'id_admin' ] );

				$diff = $card[ 'pexcard_amount' ] * -1;

				$card = array_merge( $card, [ 'login' => $admin->login,
																			'orders' => 0,
																			'driver' => $admin->name,
																			'diff' => $diff,
																			'card_cash_amount' => 0,
																			'email' => $admin->email,
																			'sort' => $admin->name,
																			'card_amount' => 0 ] );
			}
			$card[ 'details' ] = false;

			$report[] = $card;
		}

		usort( $report, function( $a, $b ){
			return $a[ 'sort' ] > $b[ 'sort' ];
		} );

		echo json_encode( [ 'drivers_expenses' => $report ] );exit;
	}

	public function saveTransaction( $transaction ){

		if( $transaction->id ){

			$_transaction = Crunchbutton_Pexcard_Transaction::getByTransactionId( $transaction->id );

			// if it is not new some fields may need to be updated
			if( !$_transaction->id_pexcard_transaction ){
				$_transaction = new Crunchbutton_Pexcard_Transaction();
			}

			$transactionTime = date( 'Y-m-d H:i:s', strtotime( $transaction->transactionTime ) );
			$settlementTime = date( 'Y-m-d H:i:s', strtotime( $transaction->settlementTime ) );

			$date = new DateTime( $transactionTime, new DateTimeZone( 'America/Chicago' ) );
			$date->setTimezone( new DateTimeZone( c::config()->timezone ) );
			$_transaction->transactionTime_pst = $date->format( 'Y-m-d H:i:s' );

			$_transaction->transactionId = $transaction->id;
			$_transaction->acctId = $transaction->acctId;
			$_transaction->transactionTime = $transactionTime;
			$_transaction->settlementTime = $settlementTime;
			$_transaction->transactionCode = $transaction->transactionCode;
			$_transaction->firstName = $transaction->firstName;
			$_transaction->middleName = $transaction->middleName;
			$_transaction->lastName = $transaction->lastName;
			$_transaction->transactionCode = $transaction->transactionCode;
			$_transaction->cardNumber = $transaction->cardNumber;
			$_transaction->spendCategory = $transaction->spendCategory;
			$_transaction->description = $transaction->description;
			$_transaction->amount = ( $transaction->amount * -1 );

			// v4 api fields
			$_transaction->transferToOrFromAccountId = $transaction->transferToOrFromAccountId;
			$_transaction->transactionType = $transaction->transactionType;
			$_transaction->isPending = ( $transaction->isPending ) ? 1 : 0;
			$_transaction->isDecline = ( $transaction->isDecline ) ? 1 : 0;
			$_transaction->paddingAmount = $transaction->paddingAmount;
			$_transaction->merchantName = $transaction->merchantName;
			$_transaction->merchantCity = $transaction->merchantCity;
			$_transaction->merchantState = $transaction->merchantState;
			$_transaction->merchantCountry = $transaction->merchantCountry;
			$_transaction->MCCCode = $transaction->MCCCode;
			$_transaction->authTransactionId = $transaction->authTransactionId;

			$_transaction->save();

			return $_transaction;
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'pexcard_transaction' )->idVar( 'id_pexcard_transaction' )->load( $id );
	}
}

?>
