<?php

class Controller_Api_Test_Sandbox extends Crunchbutton_Controller_Account {

	public function init() {



		$admin = Admin::o( 5 );

		$paymenType = $admin->paymenType();

		$paymenType->testAccount();
die('hard');

		$restaurant = Restaurant::o( 107 );
		$paymentType = $restaurant->payment_type();

		$stripeAccount = $paymentType->getStripe();

		$stripeAccount->bank_account = 'btok_64TigicYqgKovr';
		$stripeAccount->save();

		$paymentType->stripe_account_id = $stripeAccount->bank_accounts->data[0]->id;
		$paymentType->save();


		$stripeAccount = $paymentType->getStripe();

		echo '<pre>';var_dump( $stripeAccount );exit();



// 		$charge = \Stripe\Charge::create(array(
//   "amount" => 10000,
//   "currency" => "usd",
//   "source" => "tok_15sJQsJMXBWnTQ4r79v0Kgbx", // obtained with Stripe.js
//   "description" => "Charge for test@example.com"
// ));
// 		echo '<pre>';var_dump( $charge );exit();


// $transfer = \Stripe\Transfer::create(array(
// 		  "amount" => 1000, // amount in cents
// 		  "currency" => "usd",
// 		  "destination" => 'acct_15sILuDanUD1ASbC',
// 		  // "bank_account" => 'ba_15sILuDanUD1ASbCYqqugN3G',
// 		  "statement_descriptor" => "Testing")
// 		);
// echo '<pre>';var_dump( $transfer );exit();
		// $stripeAccount = \Stripe\Account::retrieve( 'acct_15sHjgBSfd8TiLtT' );

		// echo '<pre>';var_dump( $stripeAccount->bank_accounts->count() );exit();
		// echo '<pre>';var_dump( $stripeAccount->bank_accounts->data[0]->id );exit();

		// $stripeAccount->bank_account = $stripeBankToken;

		// $stripeAccount->save();

		// echo '<pre>';var_dump( $stripeAccount );exit();

		// $bank_account = 'btok_64Qt4baB4vDXV4';

		$restaurant = Restaurant::o( 107 );

		$paymentType = $restaurant->payment_type();

		$stripe = $paymentType->stripeTransfer( 100 );
		// $stripe = $paymentType->getStripe();

		echo '<pre>';var_dump( $stripe );exit();

		// $ip = c::db()->get('select session.* from session where id_admin=5 and ip is not null order by session.date_activity desc limit 1')->get(0)->ip;

		$params = [ 'bank_account' => $bank_account, 'account_type' => 'individual', 'ip' => null, 'dob' => [ 'year' => 1981, 'month' => 8, 'day' => 17 ], 'ssn' => null ];

		echo '<pre>';var_dump( $paymentType->createStripe( $params ) );exit();;


$stripeBankToken = $bank->meta->{'stripe_customer.funding_instrument.id'};

$paymentType->stripe_id = $stripeAccountId;
		$paymentType->stripe_account_id = $stripeBankAccount->id;
		$paymentType->user()->stripe_id = $stripeAccountId;

$stripeAccountId = $stripeAccount->id;

die('hard');

		try {
			$bank = Crunchbutton_Balanced_BankAccount::byId($paymentType->balanced_bank);
		} catch (Exception $e) {
			echo "ERROR: Failed to get balanced id\n";
			continue;
		}



		// Create a transfer to the specified recipient
		$transfer = \Stripe\Transfer::create(array(
		  "amount" => 1000, // amount in cents
		  "currency" => "usd",
		  "recipient" => 'rp_15sFdPJMXBWnTQ4r8KsQgby1',
		  "bank_account" => 'ba_15sFdCJMXBWnTQ4rDVBYSciO',
		  "statement_descriptor" => "Testing")
		);

		echo '<pre>';var_dump( $transfer );exit();

	}
}