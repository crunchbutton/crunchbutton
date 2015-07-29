CREATE TABLE `resource` (
  `id_resource` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `all` tinyint(1) NOT NULL DEFAULT '0',
  `page` tinyint(1) NOT NULL DEFAULT '0',
  `side` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `order_page` tinyint(1) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_resource`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `resource_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `resource_community` (
  `id_resource_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_resource` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_resource_community`),
  KEY `resource_community_ibfk_1` (`id_community`),
  KEY `resource_community_ibfk_2` (`id_resource`),
  CONSTRAINT `resource_community_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `resource_community_ibfk_2` FOREIGN KEY (`id_resource`) REFERENCES `resource` (`id_resource`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE community_resource_community;

DROP TABLE community_resource;
