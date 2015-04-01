<?php


$users = User::q('select user.* from user left join user_payment_type using(id_user)
where user.balanced_id is not null
and user_payment_type.balanced_id is null');

foreach ($users as $user) {

	if (!$user->balanced_id) {
		continue;
	}
	$paymentType = (new User_Payment_Type([
		'id_user' => $user->id_user,
		'active' => 1,
		'stripe_id' => $user->stripe_id,
		'balanced_id' => $user->balanced_id,
		'card' => $user->card,
		'card_exp_month' => $user->card_exp_month,
		'card_exp_year' => $user->card_exp_year,
		'date' => date('Y-m-d H:i:s')
	]))->save();
}