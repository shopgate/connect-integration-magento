/**
 * Creates a SOAP user to run tests against
 * Username: shopgate-tests
 * Api Key: shopgate-api-key
 */
INSERT INTO `api_user` (`firstname`, `lastname`, `email`, `username`, `api_key`, `created`, `modified`, `lognum`, `reload_acl_flag`, `is_active`) VALUES ('Shopgate', 'Tests', 'tests@shopgate.com', 'shopgate-tests', '58f75877805e185117912cd1eb9aae58', '2018-08-10 06:19:28', '2018-08-10 06:19:28', 0, 0, 1);
SET @USER_ID=LAST_INSERT_ID();

INSERT INTO `api_role` (`parent_id`, `tree_level`, `sort_order`, `role_type`, `user_id`, `role_name`) VALUES (0, 1, 0, 'G', 0, 'Shopgate TESTS');
SET @ROLE_ID=LAST_INSERT_ID();
INSERT INTO `api_role` (`parent_id`, `tree_level`, `sort_order`, `role_type`, `user_id`, `role_name`) VALUES (@ROLE_ID, 1, 0, 'U', @USER_ID, 'Shopgate');

INSERT INTO `api_rule` (`role_id`, `resource_id`, `api_privileges`, `assert_id`, `role_type`, `api_permission`) VALUES (@ROLE_ID, 'all', null, 0, 'G', 'allow');
