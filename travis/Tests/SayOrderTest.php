<?php
/*
class SayTest extends PHPUnit_Framework_TestCase { //ExampleTest
	public function setUp() {
		$this->order = Order::q('select * from `order` where name="UNIT TEST ORDER" order by id_order desc limit 1')->get(0);
	print_r($this->order);	
	}
	public static function setUpBeforeClass() {
	
		$order=new Order([
'name'=>'UNIT TEST ORDER'

]);
		$order->save();
	}

	public function testSay() { //lowercase test, uppercase wtvr
		
	
$data = file_get_contents($GLOBALS['host-crunchbutton'].'api/order/'.$this->order->id_order.'/say');

$this->assertTrue($data == '<?xml version="1.0" encoding="UTF-8"?><Response>
<Gather action="/api/order/'.$this->order->id_order.'/sayorder?id_notification=" numDigits="1" timeout="10" finishOnKey="#" method="get"><Say voice="male">Hello. This is crunchbutton, with an order for pickup.</Say><Pause length="1" /><Say voice="male">Press 1 to hear the order. Otherwise we will call back in 2 minutes.</Say><Pause length="5" /><Say voice="male">Press 1 to hear the order. Otherwise we will call back in 2 minutes.</Say><Pause length="5" /><Say voice="male">Press 1 to hear the order. Otherwise we will call back in 2 minutes.</Say><Pause length="5" /><Say voice="male">Press 1 to hear the order. Otherwise we will call back in 2 minutes.</Say><Pause length="5" /></Gather></Response>' ? true : false);
	}
	
	//new one to fill
	public function testSayOrder() { //lowercase test, uppercase wtvr
		
	$order=new Order([
'name'=>'UNIT TEST ORDER'

]);
		$order->save();
	

$data = file_get_contents($GLOBALS['host-crunchbutton'].'api/order/'.$order->id_order.'/sayorder');
echo $data;
echo '<?xml version="1.0" encoding="UTF-8"?><Response>
<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification=" numDigits="1" timeout="10" finishOnKey="#" method="get"><Say voice="male">Thank you. At the end of the message, you must confirm the order.</Say><Pause length="2" /><Say voice="male">Customer Phone number. .</Say><Pause length="1" /><Say voice="male"><![CDATA[Customer Name. UNIT TEST ORDER.]]></Say><Pause length="1" /><Say></Say><Pause length="1" /><Say voice="male">This order is for pickup. </Say><Pause length="2" /><Say voice="male"><![CDATA[
- 1st item. Wenzel .  Lettuce. Tomato. Cheese. Mayo.]]></Say><Pause length="2" /><Say voice="male"><![CDATA[.]]></Say><Pause length="1" /><Say voice="male">Order total:  dollars and  cents</Say><Pause length="1" /><Say voice="male">The customer will be paying the tip . by cash.</Say><Pause length="1" /><Say voice="male">The customer will pay for this order with cash.</Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say></Gather></Response>';
$this->assertTrue($data == '<?xml version="1.0" encoding="UTF-8"?><Response>
<Gather action="/api/order/'.$order->id_order.'/sayorderonly?id_notification=" numDigits="1" timeout="10" finishOnKey="#" method="get"><Say voice="male">Thank you. At the end of the message, you must confirm the order.</Say><Pause length="2" /><Say voice="male">Customer Phone number. .</Say><Pause length="1" /><Say voice="male"><![CDATA[Customer Name. UNIT TEST ORDER.]]></Say><Pause length="1" /><Say></Say><Pause length="1" /><Say voice="male">This order is for pickup. </Say><Pause length="2" /><Say voice="male"><![CDATA[
- 1st item. Wenzel .  Lettuce. Tomato. Cheese. Mayo.]]></Say><Pause length="2" /><Say voice="male"><![CDATA[.]]></Say><Pause length="1" /><Say voice="male">Order total:  dollars and  cents</Say><Pause length="1" /><Say voice="male">The customer will be paying the tip . by cash.</Say><Pause length="1" /><Say voice="male">The customer will pay for this order with cash.</Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say></Gather></Response>' ? true : false);
	}


public function testSayOrderOnly() { //lowercase test, uppercase wtvr
		
		

$data = file_get_contents($GLOBALS['host-crunchbutton'].'api/order/'.$this->order->id_order.'/sayorderonly');

$this->assertTrue($data == '<?xml version="1.0" encoding="UTF-8"?><Response>
<Gather action="/api/order/'.$this->order->id_order.'/sayorderonly?id_notification=" numDigits="1" timeout="10" finishOnKey="#" method="get"><Say voice="male">Customer Phone number. .</Say><Pause length="1" /><Say voice="male"><![CDATA[Customer Name. UNIT TEST ORDER.]]></Say><Pause length="1" /><Say></Say><Pause length="1" /><Say voice="male">This order is for pickup. </Say><Pause length="2" /><Say voice="male"><![CDATA[
- 1st item. Wenzel .  Lettuce. Tomato. Cheese. Mayo.]]></Say><Pause length="2" /><Say voice="male"><![CDATA[.]]></Say><Pause length="1" /><Say voice="male">Order total:  dollars and  cents</Say><Pause length="1" /><Say voice="male">The customer will be paying the tip . by cash.</Say><Pause length="1" /><Say voice="male">The customer will pay for this order with cash.</Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say><Pause length="1" /><Say voice="male">Press 1 to repeat the order. Press 2 to confirm the order. </Say></Gather></Response>' ? true : false);
	}
	
	public function testDoConfirm() { //lowercase test, uppercase wtvr
		
		

$data = file_get_contents($GLOBALS['host-crunchbutton'].'api/order/'.$this->order->id_order.'/doconfirm');

$this->assertTrue($data == '<?xml version="1.0" encoding="UTF-8"?>
<Response><Gather action="/api/order/'.$this->order->id_order.'/doconfirm" numDigits="1" timeout="10" finishOnKey="#" method="get"><Say voice="male">Hello. This is crunchbutton,.</Say><Say voice="male" loop="3">Please press 1 to confirm that you just received order number 167. Or press 2 and we will resend the order. . . .</Say></Gather></Response>' ? true : false);
	}
}//end file
*/