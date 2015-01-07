<?php

$data = 'phone,code
_PHONE_,BKN1
_PHONE_,BKN2';

Blast::parseCsv($data);
exit;
$blast = Blast::o(1);
$blast->run();
exit;