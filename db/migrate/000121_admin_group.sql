CREATE TABLE `admin` (
  `id_admin` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(40) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `txt` varchar(12) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;