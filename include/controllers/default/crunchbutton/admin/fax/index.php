
<?php

class Controller_admin_fax extends Crunchbutton_Controller_Account {
	public function init() {

		c::view()->layout('layout/admin');
		$r = new Restaurant($_REQUEST['id_restaurant']);
		foreach ($r->notifications() as $notification) {
			if ($notification->type == 'fax') {
				$n = $notification->value;
			}
		}
						
		$num = ($env == 'live' ? $n : '_PHONE_');

		$ext = explode('.',$_FILES['fax']['name']);
		$ext = array_pop($ext);

		$temp = tempnam('/tmp','fax');
		file_put_contents($temp, file_get_contents($_FILES['fax']['tmp_name']));
		//chmod($temp, 0777);
		rename($temp, $temp.'.'.$ext);

		$fax = new Phaxio([
			'to' => $num,
			'file' => $temp.'.'.$ext
		]);
		echo '<pre>';
		print_r($fax);

		//unlink($temp.'.'.$ext);
		exit;
	}
}