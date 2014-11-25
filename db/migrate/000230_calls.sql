
DROP TABLE IF EXISTS `call`;

CREATE TABLE `call` (
  `id_call` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(20) DEFAULT NULL,
  `to` varchar(20) DEFAULT NULL,
  `twilio_id` varchar(255) DEFAULT NULL,
  `data` text,
  `date_start` datetime DEFAULT NULL,
  `location_to` varchar(255) DEFAULT NULL,
  `location_from` varchar(255) DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `status` enum('completed','ringing','queued','in-progress','busy','failed','no-answer','canceled') DEFAULT NULL,
  `id_support` int(11) unsigned DEFAULT NULL,
  `id_admin_to` int(11) unsigned DEFAULT NULL,
  `id_user_to` int(11) unsigned DEFAULT NULL,
  `id_admin_from` int(11) unsigned DEFAULT NULL,
  `id_user_from` int(11) unsigned DEFAULT NULL,
  `direction` enum('inbound','outbound') DEFAULT NULL,
  `recording_sid` varchar(255) DEFAULT NULL,
  `recording_duration` int(11) DEFAULT NULL,
  `recording_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_call`),
  UNIQUE KEY `twilio_id` (`twilio_id`),
  KEY `id_support` (`id_support`),
  KEY `id_admin_to` (`id_admin_to`),
  KEY `id_admin_from` (`id_admin_from`),
  KEY `id_user_to` (`id_user_to`),
  KEY `id_user_from` (`id_user_from`),
  CONSTRAINT `call_ibfk_1` FOREIGN KEY (`id_support`) REFERENCES `support` (`id_support`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_2` FOREIGN KEY (`id_admin_to`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_3` FOREIGN KEY (`id_admin_from`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_4` FOREIGN KEY (`id_user_to`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `call_ibfk_5` FOREIGN KEY (`id_user_from`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
