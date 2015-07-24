CREATE TABLE `stripe_webhook_type` (
  `id_stripe_webhook_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_stripe_webhook_type`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `stripe_webhook_type` ADD INDEX `stripe_webhook_type_type` (`type`);
ALTER TABLE `stripe_webhook_type` ADD UNIQUE INDEX `stripe_webhook_type_ui` (`type`);

CREATE TABLE `stripe_webhook` (
  `id_stripe_webhook` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_stripe_webhook_type` int(11) unsigned DEFAULT NULL,
  `event_id` varchar(255) DEFAULT NULL,
  `object_id` varchar(255) DEFAULT NULL,
  `created` int DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_stripe_webhook`),
  KEY `stripe_webhook_ibfk1` (`id_stripe_webhook_type`),
  CONSTRAINT `stripe_webhook_ibfk1` FOREIGN KEY (`id_stripe_webhook_type`) REFERENCES `stripe_webhook_type` (`id_stripe_webhook_type`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `stripe_webhook` ADD INDEX `stripe_webhook_object_id` (`object_id`);
