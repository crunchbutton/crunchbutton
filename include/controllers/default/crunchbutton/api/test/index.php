<?php

class Controller_api_test extends Crunchbutton_Controller_Rest {
	public function init() {

		$reward = new Crunchbutton_Reward;
		$code = 'h64i5tnbju';
		echo '<pre>';var_dump( $reward->checkIfItIsEligibleForFirstTimeOrder() );exit();
		$valid = $reward->checkIfItIsEligibleForFirstTimeOrder();
		$valid = $reward->validateInviteCode( $code );
		echo '<pre>';var_dump( $valid );exit();




foreach (Crunchbutton_Util::frontendTemplates( false, true ) as $file) {
	echo $file."\n";
	$fi = explode('/',$file);
	array_shift($fi);
	$fi = implode('/',$fi);
	$fi = $fi ? $fi : $file;
	echo $fi;
	/*
	<script type="text/ng-template" id="assets/view/<?=$fi?>.html">
		<?=$this->render('frontend/'.$file, ['filter' => false])?>
	</script>
	*/
}
exit;

			Log::critical([
				'action' => 'max confirm callback tries ('.c::config()->twilio->maxconfirmback.') exceeded.',
				'host' => $_SERVER['__HTTP_HOST'],
				'type' => 'notification'
			]);


	exit;
		$b = ['cock'];

		c::timeout(function() use ($b) {
			print_r($b);
		}, false, false);

		exit;
		$order = new Order(635);
		$order->que();
		exit;


		c::timeout(function() {
			mail('_EMAIL','asdasdsad','fdfdfd');
		});
		exit;


		$r = Restaurant::o(11);
		echo $r->phone();
		exit;
		$r->createMerchant([
			'name' => 'Devin Smith',
			'zip' => '90292',
			'address' => '13701 marina pointe drive',
			'dob' => '1984-09'
		]);
	exit;
//		$r = Restaurant::o(1);
//		$r->saveBankInfo('321174851','1234567890','test');
		$p = Payment::credit([
			'id_restaurant' => 1,
			'amount' => 5.00,
			'note' => 'another test'
		]);

		exit;
		$q = 'select dish_option.* from dish_option left join dish using(id_dish) where dish.id_restaurant="18" and dish_option.id_dish="126"';
		$r = c::db()->query($q);
		while ($o = $r->fetch()) {

			$ob = new Dish_Option;
			$ob->id_dish = 125;
			$ob->id_option = $o->id_option;
			$ob->default = $o->default;
			$ob->save();
		}

		//$o = new Order(111);
		//$o->notify();
	}
}