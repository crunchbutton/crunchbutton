CREATE TABLE `blocked` (
  `id_blocked` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned DEFAULT NULL,
  `id_phone` int(11) unsigned DEFAULT NULL,
  `id_admin` int(11) unsigned DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_blocked`),
  KEY `id_user` (`id_user`),
  KEY `id_phone` (`id_phone`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `blocked_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blocked_ibfk_2` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blocked_ibfk_3` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `config` (`id_site`, `key`, `value`) VALUES (NULL,'blocked-customer-message','Oops, something bad happened!');
