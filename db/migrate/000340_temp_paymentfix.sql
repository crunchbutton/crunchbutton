CREATE TABLE `temp_paymentfix` (
  `id_temp_paymentfix` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `id_payment` int(11) unsigned DEFAULT NULL,
  `id_payment_schedule` int(11) unsigned DEFAULT NULL,
  `id_order` int(11) unsigned DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id_temp_paymentfix`),
  KEY `temp_paymentfix_ibfk_1` (`id_driver`),
  KEY `temp_paymentfix_ibfk_2` (`id_payment`),
  KEY `temp_paymentfix_ibfk_3` (`id_payment_schedule`),
  CONSTRAINT `temp_paymentfix_ibfk_1` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `temp_paymentfix_ibfk_2` FOREIGN KEY (`id_payment`) REFERENCES `payment` (`id_payment`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `temp_paymentfix_ibfk_3` FOREIGN KEY (`id_payment_schedule`) REFERENCES `payment_schedule` (`id_payment_schedule`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;