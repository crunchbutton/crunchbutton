<?php
class Controller_test_push extends Crunchbutton_Controller_Account {
	public function init() {
		// Report all PHP errors
		$certs = c::config()->dirs->root.'ssl/';

		
		// Instanciate a new ApnsPHP_Push object
		$push = new ApnsPHP_Push(
			ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
			$certs.'aps_development_com.crunchbutton.cockpit.pem'
		);
		
		// Set the Provider Certificate passphrase
		// $push->setProviderCertificatePassphrase('test');
		
		// Set the Root Certificate Autority to verify the Apple remote peer
		$push->setRootCertificationAuthority($certs.'entrust_root_certification_authority.pem');
		
		// Connect to the Apple Push Notification Service
		$push->connect();
		
		// Instantiate a new Message with a single recipient
		$message = new ApnsPHP_Message('b85dc7710abcd6a18aa6ff91ca165aa97fa02df23323d49c689a7d50fd47e800');
		
		// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
		// over a ApnsPHP_Message object retrieved with the getErrors() message.
		$message->setCustomIdentifier('order-recieved');
		$message->setBadge(1);
		
//		$message->setActionLocKey('Show me!');
//		$message->setLocKey('Hello %1$@, you have %2$@ new messages!'); // This will overwrite the text specified with setText() method.
//		$message->setLocArgs(array('Steve', 5));
//		$message->setCustomProperty('acme2', array('bang', 'whiz'));
		$message->setCategory('support-message-test');
		
		// Set a simple welcome text
		$message->setText('#5634: Devin has placed an order to Chipotle');
		
		// Play the default sound
		$message->setSound('www/new-order.wav');
//		$message->setSound('order.wav');

		// Set another custom property
//		$message->setCustomProperty('acme3', array('bing', 'bong'));
		
		// Set the expiry value to 30 seconds
		$message->setExpiry(30);
		
		// Add the message to the message queue
		$push->add($message);
		
		// Send all messages in the message queue
		$push->send();
		
		// Disconnect from the Apple Push Notification Service
		$push->disconnect();
		
		// Examine the error message container
		$aErrorQueue = $push->getErrors();
		if (!empty($aErrorQueue)) {
			var_dump($aErrorQueue);
		}


	}
}

