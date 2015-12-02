CREATE TABLE `chain` (
  `id_chain` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_chain`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `community_chain` (
  `id_community_chain` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_chain` int(11) unsigned DEFAULT NULL,
  `id_community` int(11) unsigned DEFAULT NULL,
  `exist_at_community` tinyint(1) NOT NULL DEFAULT '1',
  `within_range` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_community_chain`),
  KEY `id_community_chain` (`id_community_chain`),
  CONSTRAINT `community_chain_ibfk_1` FOREIGN KEY (`id_chain`) REFERENCES `chain` (`id_chain`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_chain_ibfk_2` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `restaurant_chain` (
  `id_restaurant_chain` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_chain` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_restaurant_chain`),
  KEY `id_restaurant_chain` (`id_restaurant_chain`),
  CONSTRAINT `restaurant_chain_ibfk_1` FOREIGN KEY (`id_community_chain`) REFERENCES `community_chain` (`id_community_chain`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `restaurant_chain_ibfk_2` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;