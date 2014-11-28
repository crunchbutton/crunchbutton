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

	public function save_transaction( $transaction ){

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
