CREATE TABLE `community_shift` (
  `id_community_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id_community_shift`),
  KEY `community_shift_ibfk_1` (`id_community`),
  CONSTRAINT `community_shift_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `admin_shift_preference` (
  `id_admin_shift_preference` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `ranking` TINYINT( 2 ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_shift_preference`),
  KEY `admin_shift_preference_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_preference_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_preference_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_preference_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `admin_shift_assign` (
  `id_admin_shift_assign` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_assign`),
  KEY `admin_shift_assign_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `admin_shift_status` (
  `id_admin_shift_status` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `year` int(4) unsigned DEFAULT NULL,
  `week` int(2) unsigned DEFAULT NULL,
  `completed` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `shifts` TINYINT( 2 ) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_status`),
  KEY `admin_shift_status_ibfk_1` (`id_admin`),
  CONSTRAINT `admin_shift_status_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;