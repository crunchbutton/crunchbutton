CREATE TABLE `pexcard_transaction` (
  `id_pexcard_transaction` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transactionId` int(11) unsigned DEFAULT NULL,
  `acctId` int(11) unsigned DEFAULT NULL,
  `transactionTime` datetime DEFAULT NULL,
  `settlementTime` datetime DEFAULT NULL,
  `transactionCode` int(11) unsigned DEFAULT NULL,
  `firstName` varchar(40) DEFAULT NULL,
  `middleName` varchar(40) DEFAULT NULL,
  `lastName` varchar(40) DEFAULT NULL,
  `cardNumber` int(4) unsigned DEFAULT NULL,
  `spendCategory` varchar(40) DEFAULT NULL,
  `description` varchar(40) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id_pexcard_transaction`),
  UNIQUE KEY `id_pexcard_transaction` (`id_pexcard_transaction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;