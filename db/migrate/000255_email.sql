CREATE TABLE `email_address` (
  `id_email_address` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_email_address`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `email_address_log` (
  `id_email_address_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_email_address_to` int(11) unsigned DEFAULT NULL,
  `id_email_address_from` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_email_address_log`),
  KEY `id_email_address_to` (`id_email_address_to`),
  KEY `id_email_address_from` (`id_email_address_from`),
  CONSTRAINT `email_address_log_ibfk_1` FOREIGN KEY (`id_email_address_to`) REFERENCES `email_address` (`id_email_address`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `email_address_log_ibfk_2` FOREIGN KEY (`id_email_address_from`) REFERENCES `email_address` (`id_email_address`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
