
CREATE TABLE `community_change` (
  `id_community_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_change_set` int(10) unsigned DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_community_change`),
  KEY `id_community_change_set` (`id_community_change_set`),
  CONSTRAINT `community_change_ibfk_1` FOREIGN KEY (`id_community_change_set`) REFERENCES `community_change_set` (`id_community_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `community_change_set` (
  `id_community_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_change_set`),
  KEY `id_community` (`id_community`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `community_change_set_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_change_set_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

