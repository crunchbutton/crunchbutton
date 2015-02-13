ALTER TABLE `order` MODIFY `likely_test` tinyint DEFAULT NULL;

-- then this will run on a cron job
UPDATE `order` O
JOIN community ON community.id_community = O.id_community
JOIN restaurant ON restaurant.id_restaurant = O.id_restaurant
SET likely_test = TRUE
WHERE likely_test IS NULL AND (
	community.name LIKE '%test%'
	OR restaurant.name LIKE '%test%'
	OR O.name LIKE '%test%'
)
