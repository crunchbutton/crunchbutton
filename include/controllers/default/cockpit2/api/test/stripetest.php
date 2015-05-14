<?php

$p = Crunchbutton_User::q('
	select count(p.id_user_payment_type) c, `user`.* from user_payment_type p
	left join `user` using(id_user)
	where p.balanced_id is not null
	and p.stripe_id is null
	group by user.id_user
	having c = 1
	order by c desc
	limit 10
');

foreach ($p as $user) {
	$user->tempConvertBalancedToStripe();
}

echo 'ALL DONE';