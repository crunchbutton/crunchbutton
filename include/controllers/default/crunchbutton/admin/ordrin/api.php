<?php


class Controller_admin_ordrin_api extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->useFilter(false);
		c::view()->layout('layout/blank');

		
		$dt = (isset($_POST['dT'])) ? $_POST['dT'] : '';
		
		$ordrin = new Ordrin\OrdrinApi('i_y9CtcUJzl7_GPvqNkkd2t6lt65JNQ73_FeJC7NYX0', Ordrin\OrdrinApi::TEST_SERVERS);
		
		switch ($_GET["api"]) {
			case "r":
				// don't need to do anything
			break;
			case "u":
				$ordrin->user->authenticate($_POST['email'],hash('sha256',$_POST['pass']));
			break;
			case "o":
				if(!empty($_POST['pass'])){
					$ordrin->user->authenticate($_POST['email'],hash('sha256',$_POST['pass']));
				}
				$a = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], $_POST['phone']);
				$credit_card = $ordrin::creditCard($_POST['fName'] .' '. $_POST['lName'], $_POST['expMo'], $_POST['expYr'], $_POST['cardNum'], $_POST['csc'], $a); 
		
				$details = $ordrin->restaurant->details($_POST["rid"]);
				$items = array();
				foreach($details->menu as $section) {
					foreach($section->children as $item) {
						if($item->price > 5) {
							$items[] = $ordrin::trayItem($item->id, 6);
							break;
						}
					}
					if(count($items)) {
						break;
					}
				}
		
				$tray = $ordrin::tray($items);
				
				$data = array();
				$data['request'] = array('restaurant_id'=>$_POST['rid'],'tray'=>$tray->_convertForAPI(),'tip'=>$_POST['tip'],'date'=>$dt,'em'=>$_POST['email'],'password'=>$_POST['pass'],"First Name"=>$_POST['fName'],"Last Name"=>$_POST['lName'],"addr"=>$a,"credit_card"=>$credit_card);
				$addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
				$print = $ordrin->order->submit($_POST["rid"], $tray, $_POST['tip'], $dt, $_POST["email"], $_POST['pass'], $_POST["fName"], $_POST["lName"], $a, $credit_card);
				$data['response'] = $print;
				echo json_encode($data);
			break;
		}
		if(!isset($_POST['func'])) {
			$_POST['func'] = 'ord';
		}
		switch ($_POST["func"]) {
			case "dl":
				$addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
				$print = $ordrin->restaurant->getDeliveryList($dt, $addr);
				echo json_encode($print);
			break;
			case "dc":
				$addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
				$print = $ordrin->restaurant->deliveryCheck($_POST["rid"], $dt, $addr);
				echo json_encode($print);
			break;
			case "df":
				$sT = $_POST["sT"];
				$tip = $_POST["tip"];
				$addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
				$print = $ordrin->restaurant->deliveryFee($_POST["rid"], $sT, $tip, $dt, $addr);
				echo json_encode($print);
			break;
			case "rd":
				$print = $ordrin->restaurant->details($_POST["rid"]);
				echo json_encode($print);
			break;
		
			case "gacc":
				$print = $ordrin->user->getAccountInfo();
				echo json_encode($print);
			break;
			case "macc":
				$print = $ordrin->user->create($_POST["email"], hash('sha256',$_POST["pass"]), $_POST["fName"], $_POST["lName"]);
				echo json_encode($print);
			break;
			case "upass":
				$ordrin->user->authenticate($_POST['email'],hash('sha256',$_POST['oldPass']));
				$print = $ordrin->user->updatePassword(hash('sha256',$_POST['pass']));
				echo json_encode($print);
			break;
			case "gaddr":
				$print = $ordrin->user->getAddress($_POST["addrNick"]);
				echo json_encode($print);
			break;
			case "uaddr":
				$a = $ordrin::Address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], $_POST["phone"], $_POST["addr2"]);
				$print = $ordrin->user->setAddress($_POST["addrNick"], $a);
				echo json_encode($print);
			break;
			case "daddr":
				$print = $ordrin->user->deleteAddress($_POST["addrNick"]);
				echo json_encode($print);
			break;
			case "gcar":
				$print = $ordrin->user->getCard($_POST["cardNick"]);
				echo json_encode($print);
			break;
			case "ucar":
				$a = $ordrin::Address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], $_POST["phone"], $_POST["addr2"]);
				$print = $ordrin->user->setCard($_POST["cardNick"], $_POST["fName"] . $_POST["lName"], $_POST["cardNum"], $_POST["csc"], $_POST["expMo"], $_POST["expYr"], $a);
				echo json_encode($print);
			break;
			case "dcar":
				$print = $ordrin->user->deleteCard($_POST["cardNick"]);
				echo json_encode($print);
			break;
			case "gordr":
				$print = $ordrin->user->getOrderHistory();
				echo json_encode($print);
			break;
			case "gordrs":
				$print = $ordrin->user->getOrderHistory($_POST["ordrID"]);
				echo json_encode($print);
			break;
		}
	}
}
