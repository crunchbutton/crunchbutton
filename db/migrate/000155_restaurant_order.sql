ALTER TABLE  `restaurant` ADD  `promotion_maximum` TINYINT( 1 ) NOT NULL DEFAULT '2';
ALTER TABLE  `order` ADD  `paid_with_cb_card` TINYINT( 1 ) NOT NULL DEFAULT '0';