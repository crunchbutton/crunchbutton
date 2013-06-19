CREATE TABLE `newusers` (
  `id_newusers` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `last_update` datetime NOT NULL,
  `email_to` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_newusers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `newusers` (`id_newusers`, `last_update`, `email_to`)
VALUES
	(1,'2013-05-15 11:00:57','_EMAIL');