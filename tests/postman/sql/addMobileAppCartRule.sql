/**
 * Inserts "APP10" cart sales rule with a condition: Shopgate Cart Type is equal Shopgate Mobile App
 */
INSERT INTO `salesrule` (`name`, `description`, `from_date`, `to_date`, `uses_per_customer`, `is_active`, `conditions_serialized`, `actions_serialized`, `stop_rules_processing`, `is_advanced`, `product_ids`, `sort_order`, `simple_action`, `discount_amount`, `discount_qty`, `discount_step`, `simple_free_shipping`, `apply_to_shipping`, `times_used`, `is_rss`, `coupon_type`, `use_auto_generation`, `uses_per_coupon`)
  VALUES ('app only discount', NULL, '2018-08-01', '2040-08-01', 0, 1, 'a:7:{s:4:\"type\";s:32:\"salesrule/rule_condition_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";s:1:\"1\";s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";s:10:\"conditions\";a:1:{i:0;a:5:{s:4:\"type\";s:37:\"shopgate_cloudapi/salesRule_condition\";s:9:\"attribute\";s:18:\"shopgate_cart_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:3:\"app\";s:18:\"is_value_processed\";b:0;}}}', 'a:6:{s:4:\"type\";s:40:\"salesrule/rule_condition_product_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";s:1:\"1\";s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}', 0, 1, NULL, 0, 'by_percent', 10.0000, NULL, 0, 0, 0, 0, 1, 2, 0, 0);


INSERT INTO `salesrule_website` (`rule_id`, `website_id`)
  VALUES (LAST_INSERT_ID(),1);

INSERT INTO `salesrule_customer_group` (`rule_id`, `customer_group_id`)
  VALUES
  (LAST_INSERT_ID(),0),
  (LAST_INSERT_ID(),1);

INSERT INTO `salesrule_coupon` (`rule_id`, `code`, `usage_limit`, `usage_per_customer`, `times_used`, `expiration_date`, `is_primary`, `created_at`, `type`)
VALUES (LAST_INSERT_ID(), 'APP10', NULL, NULL, 0, '0000-00-00 00:00:00', 1, '2018-08-01 00:00:00', 0);
