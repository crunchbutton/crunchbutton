ALTER TABLE `user` ADD COLUMN `invite_code_bkp` VARCHAR(50) DEFAULT NULL;

ALTER TABLE `user` ADD COLUMN `invite_code_updated` int(1) DEFAULT NULL;

UPDATE `user` u1 INNER JOIN `user` u2 ON u1.id_user = u2.id_user SET u1.invite_code_bkp = u2.invite_code;
