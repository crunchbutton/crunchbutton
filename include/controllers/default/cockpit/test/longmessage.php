<?php



class Controller_test_longmessage extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$r = Crunchbutton_Message_Push_Ios::send([
			'to' => 'bda4c763f2e2f2ec8b123a960fd2e9ecba591cf4a310253708156eed658a4bb2',
			'message' => '#5634: Devin has placed an order to Chipotle'
		]);
		print_r($r);
		exit;
		

		Crunchbutton_Message_Sms::send([
			'to' => '_PHONE_',
			'message' => 'wont go to me'
		]);
	}
	
}
/*
die('asd');


exit;

Crunchbutton_Message_Sms::send([
	'to' => '_PHONE_',
	'message' => 'The Mozambican War of Independence was an armed conflict between Portugal and the guerrilla forces of the Mozambique Liberation Front. It began on September 25, 1964, and ended with a cease fire on September 8, 1974. The war erupted from unrest and frustration amongst many indigenous Mozambican populations, who perceived foreign rule to be a form of exploitation and resented Portugals policies towards indigenous people. As successful self-determination movements spread throughout Africa after World War II, many Mozambicans became progressively nationalistic in outlook. For the other side, many enculturated indigenous Africans who were fully integrated into the social organization of Portuguese Mozambique reacted to the independentist claims with discomfort and suspicion. The ethnic Portuguese of the territory, including most of the ruling authorities, responded with increased military presence and fast-paced development projects. The Portuguese regular army held the upper hand during the conflict (propaganda pictured) but Mozambique achieved independence in 1975 after the Carnation Revolution in Portugal, ending 470 years of Portuguese colonial rule in the East African region. '
]);

exit;

Crunchbutton_Message_Call::send([
	'to' => '_PHONE_'
	
]);


exit;
*/