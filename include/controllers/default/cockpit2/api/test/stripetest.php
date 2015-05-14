<?php

$r = Restaurant::o(26);
$fails = json_decode($r->notes_owner);


foreach ($fails as $u) {
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
	$status = $user->tempConvertBalancedToStripe();
	if (!$status) {
		$fails[] = $user->id_user;
	}
}

$r->notes_owner = json_encode($fails);
$r->save();


echo "\n\nALL DONE";

if ($_REQUEST['r']) {
	echo '<script>setTimeout(function(){window.location=window.location},10000);</script>';
}