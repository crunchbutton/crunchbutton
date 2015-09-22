INSERT INTO `report` (`title`, `content`, `active`)
VALUES ('Communities closed', 'SELECT name as Community, \'yes\' as \'Closed\' FROM community WHERE close_all_restaurants AND active = 1 ORDER BY name ASC\n', 1);


INSERT INTO `report` (`title`, `content`, `active`)
VALUES ('Communities with 3rd Party Delivery restaurants closed', 'SELECT name as Community, \'yes\' as \'Closed\' FROM community WHERE close_3rd_party_delivery_restaurants AND active = 1 ORDER BY name ASC', 1);
