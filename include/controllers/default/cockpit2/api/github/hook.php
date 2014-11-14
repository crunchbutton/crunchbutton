<?php
echo `whoami`;
exec('git fetch', $o);
exec('git log -n 20', $o);
echo json_encode($o);
	
exit;