<?php
$que = Crunchbutton_Blast::getQue();

foreach ($que as $q) {
	$q->run();
}
exit;
				echo 'progress: '.$blast->progress()."\n";
				echo 'users: '.$blast->users()->count()."\n";

exit;
$data = 'phone,code
_PHONE_,BKN1
_PHONE_,BKN2';

Blast::parseCsv($data);
exit;
$blast = Blast::o(1);
$blast->run();
exit;