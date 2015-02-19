ALTER TABLE `order` ADD `likely_test` tinyint DEFAULT NULL;

-- reset our likely_test scenario
UPDATE `order` O
SET likely_test = NULL;

-- then this will run on a cron job
UPDATE `order` O
JOIN community ON community.id_community = O.id_community
JOIN restaurant ON restaurant.id_restaurant = O.id_restaurant
SET likely_test = (community.name LIKE '%test%' OR restaurant.name LIKE '%test%' OR O.name LIKE '%test%' OR (O.address LIKE '%test%' AND O.address IS NOT NULL))
WHERE likely_test IS NULL;

CREATE INDEX order_likely_test ON `order` (likely_test);
