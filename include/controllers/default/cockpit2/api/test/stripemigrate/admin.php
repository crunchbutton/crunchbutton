<?php

/**
 * This script contacts balanced and finds the associated stripe tokens
 *
 * 1. create a stripe managed account for the new driver
 * 2. add the token to the account
 * 3. store stripe ids for the user in the db
 */

exit; // no one should run this yet

// if this is set to true, it will make all the stripe managed accounts first. then the next time we run just set this to false
$CREATE_ACCOUNTS_FIRST = true;

$p = Crunchbutton_Admin_Payment_Type::q('
	select p.* from admin_payment_type p
	where legal_name_payment = "Devin Smith"
	and balanced_bank is not null
	and stripe_account_id is null
	order by p.id_admin_payment_type desc
	limit 1
');


foreach ($p as $paymentType) {
	echo "\nWorking on ".$paymentType->balanced_bank.' - admin #'.$paymentType->id_admin."\n";

	try {
		$bank = Crunchbutton_Balanced_BankAccount::byId($paymentType->balanced_bank);
	} catch (Exception $e) {
		echo "ERROR: Failed to get balanced id\n";
		continue;
	}

	$stripeBankToken = $bank->meta->{'stripe_customer.funding_instrument.id'};

	if (!$stripeBankToken) {
		echo "ERROR: No card meta.\n";
		continue;
	}
		
	if (strpos($stripeBankToken, 'btok_') === 0) {
		echo "Token is ".$stripeBankToken."\n";
	} else {
		echo "WARNING: Not sure what to do with this: ".$stripeBankToken."\n";
		continue;
	}

	$idStripe = $paymentType->id_stripe ? $paymentType->id_stripe : $paymentType->admin()->id_stripe;

	if (!$idStripe) {

		// create a stripe managed account
		try {
			$name = explode(' ',$paymentType->legal_name_payment);
			$address = explode("\n", $paymentType->address);
			$address[1] = explode(',', $address[1]);
			$address[1][1] = explode(' ', $address[1][1]);

			$ip = c::db()->get('select session.* from session where id_admin=? and ip is not null order by session.date_activity desc limit 1', [$paymentType->id_admin])->get(0)->ip;

			$dob = explode('-',$paymentType->admin()->dob);
			$ssn = substr($paymentType->admin()->ssn(), -4);

			$stripeAccount = \Stripe\Account::create([
				'managed' => true,
				'country' => 'US',
				'email' => $paymentType->summary_email ? $paymentType->summary_email : $paymentType->admin()->email,
				'tos_acceptance' => [
					'date' => time(),
					'ip' => $ip
				],
				'legal_entity' => [
					'type' => 'individual',
					'first_name' => array_shift($name),
					'last_name' => implode(' ',$name),
					'dob' => [ // @note: this viloates stripes docs but this is the correct way
						'day' => $dob[2], 
						'month' => $dob[1], 
						'year' => $dob[0]
					], 
					'ssn_last_4' => $ssn,
					'address' => [
						'line1' => $address[0], 
						'city' => $address[1][0],
						'state' => $address[1][1][0],
						'zip' => $address[1][1][1],
						'country' => 'US'
					]
				]
			]);

		} catch (Exception $e) {
			echo 'ERROR: '.$e->getMessage()."\n";
			continue;
		}
	} else {
		try {
			$stripeAccount = \Stripe\Account::retrieve($idStripe);
		} catch (Exception $e) {
			echo 'ERROR: '.$e->getMessage()."\n";
			continue;
		}
	}

	$stripeAccountId = $stripeAccount->id;
	
	echo 'Stripe account '.$stripeAccountId."\n";

	if ($stripeAccountId && !$CREATE_ACCOUNTS_FIRST) {
		// do something with the token
		
		$stripeAccount->bank_account = $stripeBankToken;
		$stripeAccount->save();
		
		foreach ($stripeAccount->bank_accounts as $stripeBankAccount) {			
			break;
		}

		// i havent decided which one i want yet
		$paymentType->stripe_id = $stripeAccountId;
		$paymentType->stripe_account_id = $stripeBankAccount->id;
		$paymentType->user()->stripe_id = $stripeAccountId;
		
		$paymentType->save();
		$paymentType->admin()->save();
	}

	echo 'Stripe bank '.$stripeBankToken."\n";
}

echo "\ndone";
exit;