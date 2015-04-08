CREATE TABLE `admin_group_change_set` (
  `id_admin_group_change_set` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_admin_group_change` int(10) unsigned DEFAULT NULL,
  `id_group` int(10) unsigned DEFAULT NULL,
  `id_author` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_group_change_set`),
  KEY `id_admin` (`id_admin`),
  KEY `id_group` (`id_group`),
  KEY `id_author` (`id_author`),
  CONSTRAINT `admin_group_change_set_ibfk_6` FOREIGN KEY (`id_author`) REFERENCES `admin` (`id_admin_author`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_group_change_set_ibfk_3` FOREIGN KEY (`id_admin_group_change_set`) REFERENCES `admin_group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_group_change_set_ibfk_4` FOREIGN KEY (`id_group`) REFERENCES `admin_group` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_group_change_set_ibfk_5` FOREIGN KEY (`id_author`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `admin_group_change` (
  `id_admin_group_change` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field` varchar(255) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `id_admin_group_change_set` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_group_change`),
  KEY `id_admin_group_change_set` (`id_admin_group_change_set`),
  CONSTRAINT `admin_group_change_ibfk_1` FOREIGN KEY (`id_admin_group_change_set`) REFERENCES `admin_group_change_set` (`id_admin_group_change_set`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;