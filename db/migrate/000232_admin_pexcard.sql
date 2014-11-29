ALTER TABLE `admin_pexcard` ADD `card_serial` varchar(10) DEFAULT NULL;
ALTER TABLE `admin_pexcard` ADD `last_four` varchar(4) DEFAULT NULL;


ALTER TABLE admin_pexcard ADD INDEX (card_serial);
ALTER TABLE admin_pexcard ADD INDEX (last_four);