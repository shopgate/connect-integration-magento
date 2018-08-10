/**
 * Creates a SOAP user to run tests against
 * Username: shopgate-tests
 * Api Key: shopgate-api-key
 */
INSERT INTO `api_user` (`firstname`, `lastname`, `email`, `username`, `api_key`, `created`, `modified`, `lognum`, `reload_acl_flag`, `is_active`) VALUES ('Shopgate', 'Tests', 'tests@shopgate.com', 'shopgate-tests', '07546ea29c23fd8237d64c9d9a63bd87487121971b40ae13fc4f74cf9ec7fe03:6X3lnoR3b1nKKvQgWmVU9E3oXSalfvvl', '2018-08-10 06:19:28', '2018-08-10 06:19:28', 0, 0, 1);
SET @USER_ID=LAST_INSERT_ID();

INSERT INTO `api_role` (`parent_id`, `tree_level`, `sort_order`, `role_type`, `user_id`, `role_name`) VALUES (0, 1, 0, 'G', 0, 'Shopgate TESTS');
INSERT INTO `api_role` (`parent_id`, `tree_level`, `sort_order`, `role_type`, `user_id`, `role_name`) VALUES (LAST_INSERT_ID(), 1, 0, 'U', @USER_ID, 'Shopgate');

SET @ROLE_ID=LAST_INSERT_ID();

INSERT INTO `api_rule` (`role_id`, `resource_id`, `api_privileges`, `assert_id`, `role_type`, `api_permission`) VALUES (@ROLE_ID, 'all', null, 0, 'G', 'allow');
