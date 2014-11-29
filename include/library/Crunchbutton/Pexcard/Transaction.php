<?php

class Crunchbutton_Pexcard_Transaction extends Crunchbutton_Pexcard_Resource {

	public function transactions( $start, $end ){
		$transactions = Crunchbutton_Pexcard_Resource::request( 'spendbytransactionreport', [ 'StartTime' => $start, 'EndTime' => $end ] );
		if( $transactions->body ){
			return $transactions->body->transactions;
		}
		else if( $transactions->message ){
			return $transactions->message;
		} else {
			return false;
		}
	}

	public function getByTransactionId( $transactionId ){
		return Crunchbutton_Pexcard_Transaction::q( 'SELECT * FROM pexcard_transaction WHERE transactionId = "' . $transactionId . '"' )->get( 0 );
	}

	public function saveTransactionsByPeriod( $start, $end ){
		$transactions = Crunchbutton_Pexcard_Transaction::transactions( $start, $end );
		foreach( $transactions as $transaction ){
			Crunchbutton_Pexcard_Transaction::saveTransaction( $transaction );
		}
		return true;
	}

	public function getOrderExpenses( $start, $end ){
		$expenses = c::db()->get( 'SELECT SUM( o.final_price - o.delivery_fee ) amount,
																			a.id_admin,
																			COUNT(1) AS orders,
																			a.name AS driver
																			FROM `order` o
																					INNER JOIN order_action oa ON o.id_order = oa.id_order
																					INNER JOIN admin_payment_type apt ON apt.id_admin = oa.id_admin
																					INNER JOIN admin a ON oa.id_admin = a.id_admin
																					WHERE
																						apt.using_pex = 1
																					AND
																						oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '"
																					AND
																						o.pay_type = "' . Crunchbutton_Order::PAY_TYPE_CREDIT_CARD . '"
																					AND
																						DATE_FORMAT( o.date, "%m/%d/%Y" ) <= "' . $start . '"
																					AND
																						DATE_FORMAT( o.date, "%m/%d/%Y" ) >= "' . $end . '"
																					GROUP BY a.id_admin ORDER BY driver' );
		return $expenses;
	}

	public function getExpensesByPeriod( $start, $end ){
		Crunchbutton_Pexcard_Transaction::saveTransactionsByPeriod( $start, $end );
		$expenses = c::db()->get( 'SELECT lastName AS card_serial, cardNumber AS last_four, SUM( amount ) AS amount
																												FROM pexcard_transaction
																													WHERE
																														DATE_FORMAT( transactionTime, "%m/%d/%Y" ) >= "' . $start . '"
																													AND
																														DATE_FORMAT( transactionTime, "%m/%d/%Y" ) <= "' . $end . '"
																												GROUP BY lastName, cardNumber
																												ORDER BY amount DESC' );
		return $expenses;
	}

	public function processExpenses( $start, $end ){
		$pex_expenses = Crunchbutton_Pexcard_Transaction::getExpensesByPeriod( $start, $end );
		$order_expenses = Crunchbutton_Pexcard_Transaction::getOrderExpenses( $start, $end );
		$cards_expenses = [];
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
						}
					}
					$card_amount += $amount;
					$_cards[] = [ 'card_serial' => $card->card_serial, 'last_four' => $card->last_four, 'amount' => $amount ] ;
				}
				$cards_expenses[] = [ 'id_admin' => $order_expense->id_admin, 'driver' => $order_expense->driver, 'card_amount' => number_format( $card_amount, 2 ), 'orders_amount' => number_format( $order_expense->amount, 2 ), 'orders' => $order_expense->orders, 'cards' => $_cards ];
			}
		}
		return $cards_expenses;
	}

	public function saveTransaction( $transaction ){

		if( $transaction->id ){
			$_transaction = Crunchbutton_Pexcard_Transaction::getByTransactionId( $transaction->id );
			if( !$_transaction->id_pexcard_transaction ){
				$_transaction = new Crunchbutton_Pexcard_Transaction();

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
				$_transaction->save();

			}
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
