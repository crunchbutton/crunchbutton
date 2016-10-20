ALTER TABLE `support_message` CHANGE `type` `type` enum('sms','note','auto-reply','warning','email','first-party-delivery-auto-reply') DEFAULT NULL;

