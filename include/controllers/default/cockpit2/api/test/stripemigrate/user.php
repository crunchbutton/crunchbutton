<?php

/**
 * This script contacts balanced and finds the associated stripe tokens, accounts, and cards
 *
 * 1.
 * a) if there is a balanced customer id (CU or AC) get the card from it. then update stripe with the name and email.
 * b) if there is a balanced card (CC) retrieve the stripe token (tok_) , create a customer, and add that token
 *
 * 2. store stripe ids for the user in the db
 */

exit; // no one should run this yet

$p = Crunchbutton_User_Payment_Type::q('
	select p.* from user_payment_type p
	left join `user` using(id_user)
	where name like "%TEST%"
	and p.balanced_id is not null
	and p.stripe_id is null
	group by user.id_user
	order by p.id_user_payment_type desc
	limit 1
');


foreach ($p as $paymentType) {
	echo "\nWorking on ".$paymentType->balanced_id.' - user #'.$paymentType->id_user."\n";

	try {
		if (substr($paymentType->balanced_id,0,2) != 'CC') {
			// CU or AC. who knows wtf the dif is.
			$account = Crunchbutton_Balanced_Account::byId($paymentType->balanced_id);
			$cards = $account->cards;

			if (get_class($cards) == 'RESTful\Collection') {
				foreach ($cards as $c) {
					$card = $c;
				}
			}
		} else {
			$card = Crunchbutton_Balanced_Card::byId($paymentType->balanced_id);
		}

	} catch (Exception $e) {
		echo "ERROR: Failed to get balanced id\n";
		continue;
	}

	$stripeCardId = $card->meta->{'stripe_customer.funding_instrument.id'};

	if (!$stripeCardId) {
		echo "ERROR: No card meta.\n";
		continue;
	}

	$paymentType->stripe_id = $stripeCardId;
	
	if ($account) {
		$stripeAccountId = $account->meta->{'stripe.customer_id'};
	}
	
	if (!$stripeAccountId) {
		try {
			$stripeAccount = \Stripe\Customer::create([
				'description' => $paymentType->user()->name,
				'email' => $paymentType->user()->email,
				'source' => $stripeCardId
			]);
		} catch (Exception $e) {
			echo 'ERROR: '.$e->getMessage()."\n";
			continue;
		}

		$stripeAccountId = $stripeAccount->id;
		
	} else {
		if ($account) {
			$account->description = $paymentType->user()->name;
			$account->email = $paymentType->user()->email;
			$account->save();
		}
	}
	
	$paymentType->user()->stripe_id = $stripeAccountId;
	$paymentType->user()->save();
	$paymentType->save();
	
	echo 'Stripe IDs: card '.$stripeCardId.' - account '.$stripeAccountId."\n";
}

echo "\ndone";
exit;