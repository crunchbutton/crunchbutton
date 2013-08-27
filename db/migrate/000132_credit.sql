ALTER TABLE `credit` ADD `id_referral` INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `credit` ADD INDEX (  `id_referral` );
ALTER TABLE `credit` ADD CONSTRAINT credit_ibfk_10 FOREIGN KEY (id_referral) REFERENCES `referral`(id_referral);