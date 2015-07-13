CREATE TABLE `order_logistics_parking` (
  `id_order_logistics_parking` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` TINYINT(11) unsigned DEFAULT 0,
  `parking_duration` SMALLINT(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id_order_logistics_parking`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_parking_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `order_logistics_ordertime` (
  `id_order_logistics_ordertime` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` TINYINT(11) unsigned DEFAULT 0,
  `order_time` SMALLINT(11) unsigned DEFAULT 0,
  `scale_factor` FLOAT unsigned DEFAULT 1,
  PRIMARY KEY (`id_order_logistics_ordertime`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_ordertime_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_logistics_communityspeed` (
  `id_order_logistics_communityspeed` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` TINYINT(11) unsigned DEFAULT 0,
  `mph` TINYINT(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id_order_logistics_communityspeed`),
  KEY `id_community_idx` (`id_community`),
  CONSTRAINT `order_logistics_communityspeed_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_logistics_badaddress` (
  `id_order_logistics_badaddress` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `address_lc` text NULL,
  `lat` float NULL,
  `lon` float NULL,
  PRIMARY KEY (`id_order_logistics_badaddress`),
  KEY `id_community_idx` (`id_community`),
  CONSTRAINT `order_logistics_badaddress_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_logistics_cluster` (
  `id_order_logistics_cluster` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_restaurant_cluster` int(11) unsigned DEFAULT NULL,
  `id_restaurant` int(11) unsigned DEFAULT NULL,
  `time_start_community` time NOT NULL,
  `time_end_community` time NOT NULL,
  `day_of_week` TINYINT(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id_order_logistics_cluster`),
  KEY `id_restaurant_cluster_idx` (`id_restaurant_cluster`),
  KEY `id_restaurant_idx` (`id_restaurant`),
  CONSTRAINT `order_logistics_cluster_ibfk_1` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurant` (`id_restaurant`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `admin_score` (
  `id_admin_score` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `score` float NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_admin_score`),
  KEY `id_admin_idx` (`id_admin`),
  CONSTRAINT `admin_score` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `order_logistics_fakecustomer` (
  `id_order_logistics_fakecustomer` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `lat` float NULL,
  `lon` float NULL,
  PRIMARY KEY (`id_order_logistics_fakecustomer`),
  KEY `id_community_idx` (`id_community`),
  CONSTRAINT `order_logistics_fakecustomer_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

