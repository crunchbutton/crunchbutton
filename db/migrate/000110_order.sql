ALTER TABLE `order` ADD `pay_if_refunded` TINYINT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE `order` ADD `dont_charge_fee` TINYINT( 1 ) NOT NULL DEFAULT  '0';