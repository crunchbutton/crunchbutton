CREATE TABLE `campus_cash_log` (
  `id_campus_cash_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_payment_type` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `action` enum('retrieved','deleted') DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id_campus_cash_log`),
  KEY `campus_cash_log_ibfk_1` (`id_admin`),
  CONSTRAINT `campus_cash_log_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `campus_cash_log_ibfk_2` FOREIGN KEY (`id_user_payment_type`) REFERENCES `user_payment_type` (`id_user_payment_type`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;