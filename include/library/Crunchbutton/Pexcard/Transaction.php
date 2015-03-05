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
		return Crunchbutton_Pexcard_Transaction::q( 'SELECT * FROM pexcard_transaction WHERE transactionId = "' . $transactionId . '"' )->get( 0 );
	}

	public function loadTransactions(){
		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$end = $now->format( 'm/d/Y' );
		$now->modify( '-7 days' );
		$start = $now->format( 'm/d/Y' );
		Crunchbutton_Pexcard_Transaction::saveTransactionsByPeriod( $start, $end );
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
		$expenses = c::db()->get( 'SELECT SUM( o.final_price - o.delivery_fee ) amount,
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
											AND
												oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '"
											' . $where . '
											AND
												DATE_FORMAT( o.date, "%Y-%m-%d %H:%i" ) >= "' . $start . '"
											AND
												DATE_FORMAT( o.date, "%Y-%m-%d %H:%i" ) <= "' . $end . '"
											GROUP BY a.id_admin ORDER BY driver' );
		return $expenses;
	}

	public function getExpensesByPeriod( $start, $end ){
		$query = 'SELECT acctId, SUM( amount ) AS amount
								FROM pexcard_transaction
									WHERE
										DATE_FORMAT( transactionTime, "%Y-%m-%d %H:%i" ) BETWEEN "' . $start . '" AND "' . $end . '"
										AND transactionType != "Transfer"
										AND isPending IS NULL
								GROUP BY acctId
								ORDER BY amount DESC';
		$expenses = c::db()->get( $query );
		return $expenses;
	}

		public function getExpensesByPeriodByCard( $start, $end, $acctId ){
		$query = 'SELECT *
								FROM pexcard_transaction
									WHERE
										DATE_FORMAT( transactionTime, "%Y-%m-%d %H:%i" ) BETWEEN "' . $start . '" AND "' . $end . '"
										AND transactionType != "Transfer"
										AND acctId = "' . $acctId . '"
										AND isPending IS NULL
								ORDER BY transactionTime DESC';
		$expenses = c::db()->get( $query );
		return $expenses;
	}

	public function processExpenses( $start, $end ){

		$sameDay = false;
		if( $start == $end ){
			$sameDay = true;
		}

		$start = explode( '/' , $start );
		$start = new DateTime( $start[ 2 ] . '-' . $start[ 0 ] . '-' . $start[ 1 ] . ' 00:00:00', new DateTimeZone( c::config()->timezone ) );

		$end = explode( '/' , $end );
		$end = new DateTime( $end[ 2 ] . '-' . $end[ 0 ] . '-' . $end[ 1 ] . ' 04:00:00', new DateTimeZone( c::config()->timezone ) );
		if( $sameDay ){
			$end->modify( '+ 24 hours' );
		}

		$pst_start = $start->format( 'Y-m-d H:i' );
		$pst_end = $end->format( 'Y-m-d H:i' );

		$start->setTimezone( new DateTimeZone( 'America/New_York' ) );
		$end->setTimezone( new DateTimeZone( 'America/New_York' ) );

		$est_start = $start->format( 'Y-m-d H:i' );
		$est_end = $end->format( 'Y-m-d H:i' );

		$pex_expenses = Crunchbutton_Pexcard_Transaction::getExpensesByPeriod( $est_start, $est_end );
		$order_expenses = Crunchbutton_Pexcard_Transaction::getOrderExpenses( $pst_start, $pst_end );
		$order_expenses_cash_card = Crunchbutton_Pexcard_Transaction::getOrderExpenses( $pst_start, $pst_end, false );

		$cards = [];
		$report = [];

		foreach( $pex_expenses as $card ){

			$pexcard = Cockpit_Admin_Pexcard::getByPexcard( $card->acctId );
			$info = [ 'id_pexcard' => intval( $card->acctId ) ];
			$info[ 'card_serial' ] = intval( $pexcard->card_serial );
			$info[ 'last_four' ] = $pexcard->last_four;
			$info[ 'id_admin' ] = $pexcard->id_admin;
			$info[ 'pexcard_amount' ] = floatval( number_format( $card->amount, 2 ) );

			// $info[ 'show_transactions' ] = false;
			// $info[ 'transactions' ] = [];

			// $admin = $pexcard->admin();
			// if( $admin->timezone ){
			// 	$timezone = $admin->timezone;
			// } else {
			// 	$timezone = c::config()->timezone;
			// }

			// $transactions = Crunchbutton_Pexcard_Transaction::getExpensesByPeriodByCard( $est_start, $est_end, $card->acctId );
			// foreach( $transactions as $transaction ){
			// 	$date = new DateTime( $transaction->transactionTime, new DateTimeZone( 'America/New_York' ) );
			// 	$date->setTimezone( new DateTimeZone( $timezone ) );
			// 	$info[ 'transactions' ][] = [ 'date' => $date->format( 'M jS Y g:i:s A T' ),
			// 														'description' => $transaction->description,
			// 														'amount' => $transaction->amount ];
			// }

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
			foreach( $order_expenses as $order ){
				if( intval( $card[ 'id_admin' ] ) == intval( $order->id_admin ) ){
					$diff = $card[ 'pexcard_amount' ] - floatval( number_format( $order->amount, 2 ) );
					$card = array_merge( $card, [ 'login' => $order->login,
																				'orders' => intval( $order->orders ),
																				'driver' => $order->driver,
																				'diff' => ( $diff  * -1 ),
																				'email' => $order->email,
																				'sort' => $order->driver,
																				'card_amount' => floatval( number_format( $order->amount, 2 ) ) ] );
				}
			}
			foreach( $order_expenses_cash_card as $order ){
				if( intval( $card[ 'id_admin' ] ) == intval( $order->id_admin ) ){
					$card = array_merge( $card, [ 'card_cash_amount' => floatval( number_format( $order->amount, 2 ) ) ] );
				}
			}

			if( !$card[ 'driver' ] ){

				$admin = Admin::o( $card[ 'id_admin' ] );

				$card = array_merge( $card, [ 'login' => $admin->login,
																			'orders' => 0,
																			'driver' => $admin->name,
																			'diff' => 0,
																			'card_cash_amount' => 0,
																			'email' => $admin->email,
																			'sort' => $admin->name,
																			'card_amount' => 0 ] );
			}
			$report[] = $card;
		}

		// echo '<pre>';var_dump( $report );exit();

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
