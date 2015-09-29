CREATE TABLE `community_note` (
  `id_community_note` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_community` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `text` text,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_community_note`),
  KEY `id_community` (`id_community`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `community_note_ibfk_1` FOREIGN KEY (`id_community`) REFERENCES `community` (`id_community`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `community_note_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;