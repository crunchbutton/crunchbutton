CREATE TABLE `agent` (
  `id_agent` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `browser` varchar(40) DEFAULT NULL,
  `version` varchar(40) DEFAULT NULL,
  `os` varchar(40) DEFAULT NULL,
  `engine` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id_agent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `order` ADD `id_agent` INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `order` ADD INDEX (  `id_agent` );
ALTER TABLE `order` ADD CONSTRAINT order_ibfk_4 FOREIGN KEY (id_agent) REFERENCES `agent`(id_agent);