CREATE TABLE `quote` (
  `id_quote` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(50) DEFAULT NULL,
  `quote` TEXT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `pages` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_quote`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `quote_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `quote_community` (
  `id_quote_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_quote` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_quote_community`),
  KEY `quote_community_ibfk_1` (`id_community`),
  KEY `quote_community_ibfk_2` (`id_quote`),
  CONSTRAINT `quote_community_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `quote_community_ibfk_2` FOREIGN KEY (`id_quote`) REFERENCES `quote` (`id_quote`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;