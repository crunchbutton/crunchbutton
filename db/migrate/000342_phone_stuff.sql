ALTER TABLE `user` ADD COLUMN `id_phone` int(11) unsigned DEFAULT NULL;
ALTER TABLE `user` ADD KEY `id_phone` (`id_phone`);
ALTER TABLE `user` ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `support` ADD COLUMN `id_phone` int(11) unsigned DEFAULT NULL;
ALTER TABLE `support` ADD KEY `id_phone` (`id_phone`);
ALTER TABLE `support` ADD CONSTRAINT `support_ibfk_9` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `support_message` ADD COLUMN `id_phone` int(11) unsigned DEFAULT NULL;
ALTER TABLE `support_message` ADD KEY `id_phone` (`id_phone`);
ALTER TABLE `support_message` ADD CONSTRAINT `support_message_ibfk_3` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `admin` ADD COLUMN `id_phone` int(11) unsigned DEFAULT NULL;
ALTER TABLE `admin` ADD KEY `id_phone` (`id_phone`);
ALTER TABLE `admin` ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `order` ADD COLUMN `id_phone` int(11) unsigned DEFAULT NULL;
ALTER TABLE `order` ADD KEY `id_phone` (`id_phone`);
ALTER TABLE `order` ADD CONSTRAINT `order_ibfk_9` FOREIGN KEY (`id_phone`) REFERENCES `phone` (`id_phone`) ON DELETE CASCADE ON UPDATE CASCADE;


INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM support t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone );

INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM support_message t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone );

INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM user t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone );

INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM `order` t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone );

INSERT INTO phone ( phone ) SELECT phone FROM ( SELECT DISTINCT( REPLACE( REPLACE( REPLACE( REPLACE( t.phone, ' ', '' ), ')', '' ), '(', '' ), '-', '' ) ) AS phone FROM admin t WHERE t.phone IS NOT NULL ) phone WHERE NOT EXISTS ( SELECT p.phone FROM phone p WHERE phone.phone = p.phone );


UPDATE user t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL;

UPDATE admin t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL;

UPDATE support t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL;

UPDATE support_message t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL;

UPDATE `order` t INNER JOIN phone p ON p.phone = t.phone SET t.id_phone = p.id_phone WHERE t.id_phone IS NULL;