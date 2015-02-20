-- internal join on phone for getting first order
CREATE INDEX phone_id_order ON `order` (phone, id_order);
-- subsumed by next index
DROP INDEX order_likely_test ON `order`;
-- needed to speed most metrics queries
CREATE INDEX date_community_test ON `order` (likely_test, date, id_community);
