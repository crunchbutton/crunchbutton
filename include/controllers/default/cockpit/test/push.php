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
		$message = new ApnsPHP_Message('bda4c763f2e2f2ec8b123a960fd2e9ecba591cf4a310253708156eed658a4bb2');
		
		// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
		// over a ApnsPHP_Message object retrieved with the getErrors() message.
		$message->setCustomIdentifier('order-recieved');
		$message->setBadge(1);
		
		// Set a simple welcome text
		$message->setText('#5634: Devin has placed an order to Chipotle');
		
		// Play the default sound
		$message->setSound('www/order.wav');
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

