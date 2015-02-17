CREATE TABLE `admin_shift_assign_permanently_removed` (
  `id_admin_shift_assign_permanently_removed` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community_shift` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_admin_shift_assign_permanently_removed`),
  KEY `admin_shift_assign_permanently_removed_ibfk_1` (`id_community_shift`),
  KEY `admin_shift_assign_permanently_removed_ibfk_2` (`id_admin`),
  CONSTRAINT `admin_shift_assign_permanently_removed_ibfk_1` FOREIGN KEY (`id_community_shift`) REFERENCES `community_shift` (`id_community_shift`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `admin_shift_assign_permanently_removed_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;