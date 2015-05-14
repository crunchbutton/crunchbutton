<?php

$badusers = [
	7497,
	7507,
	2549,
	7485,
	7515,
	7517,
	7518,
	7520,
	7526
];

foreach ($badusers as $u) {
	$q .= ' and `user`.id_user != '.$u.' ';
}

$p = Crunchbutton_User::q('
	select count(p.id_user_payment_type) c, `user`.* from user_payment_type p
	left join `user` using(id_user)
	where p.balanced_id is not null
	and p.stripe_id is null
	'.$q.'
	group by user.id_user
	having c = 1
	order by c desc
	limit 20
');

foreach ($p as $user) {
	$user->tempConvertBalancedToStripe();
}

echo "\n\nALL DONE";