CREATE TABLE `pexcard_cache` (
  `id_pexcard_cache` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `acctId` int(11) unsigned,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data` text,
  PRIMARY KEY (`id_pexcard_cache`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;