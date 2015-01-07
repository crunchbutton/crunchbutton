
CREATE TABLE `blast` (
  `id_blast` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text,
  `status` enum('new','blasting','complete','failed','canceled') DEFAULT NULL,
  `type` enum('email','phone') DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `update` datetime DEFAULT NULL,
  PRIMARY KEY (`id_blast`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `blast_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `blast_user` (
  `id_blast_user` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_blast` int(11) unsigned DEFAULT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(20) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id_blast_user`),
  KEY `id_blast` (`id_blast`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `blast_user_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `blast_user_ibfk_1` FOREIGN KEY (`id_blast`) REFERENCES `blast` (`id_blast`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `blast_user_log` (
  `id_blast_user_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_blast_user` int(11) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_blast_user_log`),
  KEY `id_blast_user` (`id_blast_user`),
  CONSTRAINT `blast_user_log_ibfk_1` FOREIGN KEY (`id_blast_user`) REFERENCES `blast_user` (`id_blast_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

