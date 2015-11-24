ALTER TABLE `phone_log` ADD `twilio_id` varchar(50) DEFAULT NULL;
ALTER TABLE `phone_log` ADD `status` enum('accepted', 'queued', 'sending', 'sent','delivered', 'received') DEFAULT NULL;
