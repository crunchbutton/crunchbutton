CREATE TABLE `community_closed_reason` (
  `id_community_closed_reason` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_driver` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` enum('all_restaurants','close_3rd_party_delivery_restaurants') DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_community_closed_reason`),
  KEY `community_closed_reason_ibfk_1` (`id_community`),
  KEY `community_closed_reason_ibfk_2` (`id_admin`),
  KEY `community_closed_reason_ibfk_3` (`id_driver`),
  CONSTRAINT `community_closed_reason_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_closed_reason_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_closed_reason_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;