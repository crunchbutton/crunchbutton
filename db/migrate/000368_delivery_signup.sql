CREATE TABLE `delivery_signup` (
  `id_delivery_signup` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `town` varchar(255) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `restaurants` TEXT DEFAULT NULL,
  `status` enum('new','deleted','archived','review') NOT NULL DEFAULT 'new',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_delivery_signup`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;