CREATE TABLE `community_resource` (
  `id_community_resource` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `all` tinyint(1) NOT NULL DEFAULT '0',
  `page` tinyint(1) NOT NULL DEFAULT '0',
  `side` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_community_resource`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `community_resource_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `community_resource_community` (
  `id_community_resource_community` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_community_resource` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_community_resource_community`),
  KEY `community_resource_community_ibfk_1` (`id_community`),
  KEY `community_resource_community_ibfk_2` (`id_community_resource`),
  CONSTRAINT `community_resource_community_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `community_resource_community_ibfk_2` FOREIGN KEY (`id_community_resource`) REFERENCES `community_resource` (`id_community_resource`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE  `community_resource` ADD  `active` TINYINT( 1 ) NOT NULL DEFAULT '0';
UPDATE community_resource SET active = 1;

ALTER TABLE  `community_resource` ADD  `order_page` TINYINT( 1 ) NOT NULL DEFAULT '0';
UPDATE community_resource SET active = 1;