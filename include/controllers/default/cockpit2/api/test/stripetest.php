<?php

$badusers = [
	7497, // using some wierd old balanced format
	7507,
	2549,
	7485,
	7515,
	7517,
	7518,
	7520,
	7526,
	110593, // declined cards
	110149,
	112159,
	111568,
	111313
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
	order by c desc, `user`.id_user desc
	limit '.($_REQUEST['l'] ? $_REQUEST['l'] : 200).'
');

foreach ($p as $user) {
	$user->tempConvertBalancedToStripe();
}

echo "\n\nALL DONE";