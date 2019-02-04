INSERT INTO `catalog_product_option` (`product_id`, `type`, `is_require`, `sku`, `max_characters`, `file_extension`, `image_size_x`, `image_size_y`, `sort_order`)
VALUES (553, 'field', 1, 'engraving-sku', 14, null, null, null, 10);

SET @OPTION_ID=LAST_INSERT_ID();

INSERT INTO `catalog_product_option_title` (`option_id`, `store_id`, `title`)
VALUES (@OPTION_ID, 0, 'Engraving');

INSERT INTO `catalog_product_option_price` (`option_id`, `store_id`, `price`, `price_type`)
VALUES (@OPTION_ID, 0, 5.0000, 'fixed');
