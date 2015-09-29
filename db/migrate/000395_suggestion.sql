ALTER TABLE `suggestion` CHANGE `type` `type` ENUM( 'dish', 'restaurant', 'email', 'suggestion')  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT 'dish';
