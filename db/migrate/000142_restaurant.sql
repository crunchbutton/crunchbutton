ALTER TABLE  `restaurant` DROP  `formal_relationship`;
ALTER TABLE  `restaurant` ADD  `formal_relationship` TINYINT( 1 ) NOT NULL DEFAULT '1';