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
																					INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant AND r.formal_relationship = 0
																					WHERE
																						apt.using_pex = 1
																					AND
																						oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '"
																					' . $where . '
																					AND
																						DATE_FORMAT( o.date, "%m/%d/%Y" ) >= "' . $start . '"
																					AND
																						DATE_FORMAT( o.date, "%m/%d/%Y" ) <= "' . $end . '"
																					GROUP BY a.id_admin ORDER BY driver' );
		return $expenses;
	}

	public function getExpensesByPeriod( $start, $end ){
		$expenses = c::db()->get( 'SELECT lastName AS card_serial, cardNumber AS last_four, SUM( amount ) AS amount
																												FROM pexcard_transaction
																													WHERE
																														DATE_FORMAT( transactionTime, "%m/%d/%Y" ) BETWEEN "' . $start . '" AND "' . $end . '"
																														AND transactionType != "Transfer"
																												GROUP BY lastName, cardNumber
																												ORDER BY amount DESC' );
		return $expenses;
	}

	public function processExpenses( $start, $end ){
		$pex_expenses = Crunchbutton_Pexcard_Transaction::getExpensesByPeriod( $start, $end );
		$order_expenses = Crunchbutton_Pexcard_Transaction::getOrderExpenses( $start, $end );
		$order_expenses_cash_card = Crunchbutton_Pexcard_Transaction::getOrderExpenses( $start, $end, false );
		$drivers_expenses = [];

		$_cash_order_expenses = [];

		foreach ( $order_expenses_cash_card as $order_expense ) {
			$_cash_order_expenses[ $order_expense->id_admin ] = floatval( number_format( $order_expense->amount, 2 ) );
		}

		foreach( $order_expenses as $order_expense ){
			$cards = Cockpit_Admin_Pexcard::getByAdmin( $order_expense->id_admin );
			if( $cards->count() ){
				$_cards = [];
				$card_amount = 0;
				foreach( $cards as $card ){
					$amount = 0;
					foreach( $pex_expenses as $pex_expense ){
						if( $card->last_four == $pex_expense->last_four && $card->card_serial == $pex_expense->card_serial ){
							$amount = number_format( $pex_expense->amount, 2 );
							$pex_expense->used = true;
						}
					}
					$card_amount += $amount;
				}
				$diff = floatval( floatval( number_format( $card_amount, 2 ) ) - floatval( number_format( $order_expense->amount, 2 ) ) );
				$drivers_expenses[] = [ 'id_admin' => intval( $order_expense->id_admin ), 'login' => $order_expense->login,  'driver' => $order_expense->driver, 'email' => $order_expense->email, 'pexcard_amount' => floatval( number_format( $card_amount, 2 ) ), 'card_cash_amount' => $_cash_order_expenses[ $order_expense->id_admin ], 'card_amount' => floatval( number_format( $order_expense->amount, 2 ) ), 'diff' => $diff, 'orders' => intval( $order_expense->orders ) ];
			}
		}

		$card_expenses = [];
		foreach( $pex_expenses as $pex_expense ){
			if( !$pex_expense->used ){
				$card_expenses[ $pex_expense->card_serial ] = [ 'card_serial' => $pex_expense->card_serial, 'last_four' => $pex_expense->last_four, 'amount' => number_format( $pex_expense->amount, 2 ) ];
			}
		}
		return [ 'drivers_expenses' => $drivers_expenses, 'card_expenses' => $card_expenses ];
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
