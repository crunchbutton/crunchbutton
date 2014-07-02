CREATE TABLE `admin_payment_type` (
  `id_admin_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `payment_method` enum('deposit') NOT NULL DEFAULT 'deposit',
  `payment_type` enum('orders','hours') NOT NULL DEFAULT 'orders',
  `summary_email` varchar(255) DEFAULT NULL,
  `legal_name_payment` varchar(255) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `balanced_bank` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin_payment_type`),
  KEY `admin_payment_type_ibfk1` (`id_admin`),
  CONSTRAINT `admin_payment_type_ibfk1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;