CREATE TABLE `phone` (
  `id_phone` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id_phone`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `phone_log` (
  `id_phone_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_phone_from` int(11) unsigned DEFAULT NULL,
  `id_phone_to` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` enum('call','message') DEFAULT NULL,
  `direction` enum('outgoing','incoming') DEFAULT NULL,
  PRIMARY KEY (`id_phone_log`),
  KEY `id_phone_to` (`id_phone_to`),
  KEY `id_phone_from` (`id_phone_from`),
  CONSTRAINT `phone_log_ibfk_1` FOREIGN KEY (`id_phone_to`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `phone_log_ibfk_2` FOREIGN KEY (`id_phone_from`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;