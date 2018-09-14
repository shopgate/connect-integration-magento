/**
 * Inserts "Favorite Number" customer attribute
 */
 INSERT INTO `eav_attribute` (`entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`)
VALUES
	(1, 'favorite_number', NULL, NULL, 'varchar', NULL, NULL, 'text', 'Favorite Number', NULL, NULL, 0, 1, NULL, 0, NULL);


SET @ATTRIBUTE_ID=LAST_INSERT_ID();

	INSERT INTO `customer_eav_attribute` (`attribute_id`, `is_visible`, `input_filter`, `multiline_count`, `validate_rules`, `is_system`, `sort_order`, `data_model`, `is_used_for_customer_segment`)
VALUES
	(@ATTRIBUTE_ID, 1, NULL, 1, 'a:1:{s:16:\"input_validation\";s:7:\"numeric\";}', 0, 0, NULL, 0);

INSERT INTO `customer_form_attribute` (`form_code`, `attribute_id`)
VALUES
	('adminhtml_checkout', @ATTRIBUTE_ID),
	('adminhtml_customer', @ATTRIBUTE_ID),
	('checkout_register', @ATTRIBUTE_ID),
	('customer_account_create', @ATTRIBUTE_ID),
	('customer_account_edit', @ATTRIBUTE_ID);
