CREATE TABLE `report` (
  `id_report` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_report`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;