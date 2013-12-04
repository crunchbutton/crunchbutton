INSERT INTO `config` (`key`, `value`) VALUES ( 'rule-time-since-last-order-group', 'rule-time-order' );
INSERT INTO `config` (`key`, `value`) VALUES ( 'rule-time-since-last-order-equal-group', 'rule-order-equal' );
INSERT INTO `config` (`key`, `value`) VALUES ( 'rule-gift-card-redeemed-group', 'rule-gift-card' );

INSERT INTO `group` (`name`, `description`) VALUES ( 'rule-time-order', 'Users will receive the notification when someone order twice in a short period of time' );
INSERT INTO `group` (`name`, `description`) VALUES ( 'rule-order-equal', 'Users will receive the notification when someone order the same food in a short period of time' );
INSERT INTO `group` (`name`, `description`) VALUES ( 'rule-gift-card', 'Users will receive the notification when someone redeem two or more gift cards short period of time' );

