CREATE TABLE `admin_note` (
  `id_admin_note` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `id_admin_added` int(11) unsigned DEFAULT NULL,
  `text` TEXT DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin_note`),
  KEY `id_admin` (`id_admin`),
  KEY `id_admin_added` (`id_admin_added`),
  CONSTRAINT `admin_note_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `admin_note_ibfk_2` FOREIGN KEY (`id_admin_added`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;