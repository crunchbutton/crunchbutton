CREATE TABLE `admin_shift_preference` (
  `id_admin_shift_preference` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `ranking` TINYINT( 2 ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_shift_preference`),
  KEY `admin_shift_preference_ibfk_1` (`id_community_shift`),
  CONSTRAINT `admin_shift_preference_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_preference_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;