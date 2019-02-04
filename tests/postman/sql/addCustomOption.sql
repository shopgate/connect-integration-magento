INSERT INTO `catalog_product_option` (`product_id`, `type`, `is_require`, `sku`, `max_characters`, `file_extension`, `image_size_x`, `image_size_y`, `sort_order`)
VALUES
	(553, 'field', 1, NULL, 10, NULL, NULL, NULL, 0);

SET @OPTION_ID=LAST_INSERT_ID();

INSERT INTO `catalog_product_option_title` (`option_id`, `store_id`, `title`)
VALUES
	(@OPTION_ID, 0, 'Engraving');

UPDATE `catalog_product_entity` SET `has_options` = 1, `required_options` = 1 WHERE `entity_id` = '553';
