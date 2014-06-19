-- Settlement stuff

-- payment_schedule - new table
CREATE TABLE `payment_schedule` (
  `id_payment_schedule` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `id_payment` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `note` varchar(255) DEFAULT '',
  `payment_method` enum('deposit','check') DEFAULT NULL,
  `type` enum('restaurant','driver') DEFAULT NULL,
  `status` enum('scheduled','processing','done','error') DEFAULT NULL,
  `status_date` datetime DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_payment_schedule`),
  KEY `id_restaurant` (`id_restaurant`),
  KEY `payment_schedule_ibfk_2` (`id_admin`),
  KEY `payment_schedule_ibfk_3` (`id_driver`),
  KEY `payment_schedule_ibfk_4` (`id_payment`),
  CONSTRAINT `payment_schedule_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `payment_schedule_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `payment_schedule_ibfk_4` FOREIGN KEY (`id_payment`) REFERENCES `payment` (`id_payment`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `payment_schedule_order` (
  `id_payment_schedule_order` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment_schedule` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id_payment_schedule_order`),
  KEY `id_payment_schedule` (`id_payment_schedule`),
  KEY `payment_schedule_order_ibfk_2` (`id_order`),
  CONSTRAINT `payment_schedule_order_ibfk_1` FOREIGN KEY (`id_payment_schedule`) REFERENCES `payment_schedule` (`id_payment_schedule`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_order_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_transaction` (
  `id_order_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(11) unsigned DEFAULT NULL,
  `amt` float DEFAULT NULL,
  `type` enum('debit','credit','paid-to-restaurant','paid-to-driver') DEFAULT NULL,
  `payment_type` enum('gift','card') DEFAULT NULL,
  `source` enum('crunchbutton','restaurant') DEFAULT NULL,
  `id_user_payment_type` int(11) unsigned DEFAULT NULL,
  `txn` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_order_transaction`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `order_transaction_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `order` (`id_order`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payment_order_transaction` (
  `id_payment_order_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment` int(11) unsigned DEFAULT NULL,
  `id_order_transaction` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_payment_order_transaction`),
  KEY `id_payment` (`id_payment`),
  KEY `payment_order_transaction_ibfk_2` (`id_order_transaction`),
  CONSTRAINT `payment_order_transaction_ibfk_1` FOREIGN KEY (`id_payment`) REFERENCES `payment` (`id_payment`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_order_transaction_ibfk_2` FOREIGN KEY (`id_order_transaction`) REFERENCES `order_transaction` (`id_order_transaction`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- order table
ALTER TABLE  `order` ADD  `reimburse_cash_order` tinyint(11) unsigned DEFAULT 0;

-- payment table
ALTER TABLE  `payment` ADD  `id_admin` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `payment` ADD KEY `payment_ibfk_2` (`id_admin`);
ALTER TABLE  `payment` ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE  `payment` ADD  `summary_sent_date` datetime DEFAULT NULL;

ALTER TABLE `payment_schedule` DROP `balanced_id`;
ALTER TABLE `payment_schedule` DROP `stripe_id`;

ALTER TABLE  `payment_schedule` ADD  `log` varchar(255) DEFAULT NULL;