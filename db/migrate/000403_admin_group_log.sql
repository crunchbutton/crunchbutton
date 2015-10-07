CREATE TABLE `admin_group_log` (
  `id_admin_group_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_group` int(11) unsigned DEFAULT NULL,
  `id_admin_assigned` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `assigned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_admin_group_log`),
  KEY `admin_group_log_ibfk_1` (`id_group`),
  KEY `admin_group_log_ibfk_2` (`id_admin`),
  KEY `admin_group_log_ibfk_3` (`id_admin_assigned`),
  CONSTRAINT `admin_group_log_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_group_log_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_group_log_ibfk_3` FOREIGN KEY (`id_admin_assigned`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;