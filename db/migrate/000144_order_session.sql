ALTER TABLE `order` ADD `id_session` VARCHAR( 32 ) NULL DEFAULT NULL;
ALTER TABLE `order` ADD INDEX (  `id_session` );
ALTER TABLE `order` ADD CONSTRAINT order_ibfk_5 FOREIGN KEY (id_session) REFERENCES `session`(id_session);