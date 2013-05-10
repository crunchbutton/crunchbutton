ALTER TABLE  `support` 
	ADD  `id_order` INT UNSIGNED NULL DEFAULT NULL ,
	ADD  `id_github` INT UNSIGNED NULL DEFAULT NULL ,
	ADD  `rep_name` VARCHAR( 50 ) NULL DEFAULT NULL ,
	ADD  `description_client` VARCHAR( 10000 ) NULL DEFAULT NULL ,
	ADD  `description_cb` VARCHAR( 10000 ) NULL DEFAULT NULL ,
	ADD  `fault_of` ENUM(  'restaurant',  'customer',  'crunchbutton',  'other' ) NULL DEFAULT NULL ,
	ADD  `refunded` TINYINT( 1 ) NOT NULL DEFAULT  '0',
	ADD  `customer_happy` TINYINT( 1 ) NULL DEFAULT NULL ,
	ADD INDEX (  `id_order` ,  `id_github` ,  `rep_name` )


ALTER TABLE `support`
	ADD CONSTRAINT fk_id_order 
	FOREIGN KEY (id_order) 
	REFERENCES `order`(id_order)

CREATE TABLE `support_note` (
	 `id_support_note` int(10) unsigned NOT NULL AUTO_INCREMENT,
	 `id_support` int(10) unsigned DEFAULT NULL,
	 `text` varchar(10000) NOT NULL,
	 `visibility` enum('internal','external') NOT NULL DEFAULT 'internal',
	 `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	 PRIMARY KEY (`id_support_note`),
	 KEY `id_support` (`id_support`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

ALTER TABLE `support_note`
	ADD CONSTRAINT fk_id_support
	FOREIGN KEY (id_support)
	REFERENCES `support`(id_support)




