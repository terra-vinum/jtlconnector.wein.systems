CREATE TABLE IF NOT EXISTS `mappings`
(
    `endpoint` VARBINARY(32) NOT NULL,
    `host`     INT        NOT NULL,
    `type`     INT        NOT NULL,
    PRIMARY KEY (`endpoint`, `type`)
);

CREATE TABLE IF NOT EXISTS `categories`
(
    `id`        VARBINARY(32) NOT NULL,
    `parent_id` VARBINARY(32) NULL,
    `status`    TINYINT    NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `category_translations`
(
    `model_id`         VARBINARY(32)   NOT NULL,
    `language_iso`     VARCHAR(2)   NOT NULL,
    `name`             VARCHAR(255) NOT NULL,
    `description`      TEXT         NULL,
    `title_tag`        VARCHAR(255) NULL,
    `meta_description` VARCHAR(255) NULL,
    `meta_keywords`    VARCHAR(255) NULL,
    PRIMARY KEY (`model_id`, `language_iso`),
    FOREIGN KEY (`model_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);



CREATE TABLE IF NOT EXISTS `products`
(
    `id`                             VARBINARY(32) NOT NULL,

    -- meta
    `creation_date`                  DATETIME     DEFAULT NULL,
    `modified`                       DATETIME     DEFAULT NULL,
    `is_master_product`              TINYINT(3)   DEFAULT 0,
    `master_product_id`              VARBINARY(32) NOT NULL,
    `product_type_id`                VARBINARY(32) NOT NULL,
    `shipping_class_id`              VARBINARY(32) NOT NULL,

    -- ids
    `sku`                            VARCHAR(255) DEFAULT '',
--    `asin`                           VARCHAR(255) DEFAULT '',
   `ean`                             VARCHAR(255) DEFAULT '',
--    `epid`                           VARCHAR(255) DEFAULT '',
--    `isbn`                           VARCHAR(255) DEFAULT '',
--    `un_number`                      VARCHAR(255) DEFAULT '',
--    `upc`                            VARCHAR(255) DEFAULT '',
--    `serial_number`                  VARCHAR(255) DEFAULT '',

    -- sales
    `is_active`                      TINYINT(3)   DEFAULT 0,
    `tax_class_id`                   VARBINARY(32) NOT NULL,
    `minimum_order_quantity`         DOUBLE       DEFAULT 0.0,
    `minimum_quantity`               DOUBLE       DEFAULT 0.0,
    `vat`                            DOUBLE       DEFAULT 0.0,
    `recommended_retail_price`       DOUBLE       DEFAULT 0.0,
    `packaging_quantity`             DOUBLE       DEFAULT 0.0,

    -- wtf
--    `manufacturer_id`                varbinary(32) NOT NULL,
--    `manufacturer_number`            VARCHAR(255) DEFAULT '',
--    `measurement_unit_id`            varbinary(32) NOT NULL,
--    `parts_list_id`                  varbinary(32) NOT NULL,
--    `hazard_id_number`               VARCHAR(255) DEFAULT '',
--    `unit_id`                        varbinary(32) NOT NULL,

    -- base price
    `base_price_unit_id`             VARBINARY(32) NOT NULL,
    `base_price_divisor`             DOUBLE       DEFAULT 0.0,
    `base_price_factor`              DOUBLE       DEFAULT 0.0,
    `base_price_quantity`            DOUBLE       DEFAULT 0.0,
    `base_price_unit_code`           VARCHAR(255) DEFAULT '',
    `base_price_unit_name`           VARCHAR(255) DEFAULT '',

    -- logistics
    `additional_handling_time`       BIGINT(20)   DEFAULT 0,
    `available_from`                 DATETIME     DEFAULT NULL,
    `consider_base_price`            TINYINT(3)   DEFAULT 0,
    `discountable`                   TINYINT(3)   DEFAULT 1,

    -- stock
    `consider_stock`                 TINYINT(3)   DEFAULT 0,
    `consider_variation_stock`       TINYINT(3)   DEFAULT 0,
    `permit_negative_stock`          TINYINT(3)   DEFAULT 0,
    `stock_level`                    DOUBLE       DEFAULT 0.0,
    `supplier_delivery_time`         BIGINT(20)   DEFAULT 0,
    `supplier_stock_level`           DOUBLE       DEFAULT 0.0,

    -- physics
--    `width`                          double       DEFAULT 0.0,
--    `length`                         double       DEFAULT 0.0,
--    `height`                         double       DEFAULT 0.0,
--    `product_weight`                 double       DEFAULT 0.0,
--    `shipping_weight`                double       DEFAULT 0.0,

    -- misc.
--    `keywords`                       VARCHAR(255) DEFAULT '',
--    `measurement_quantity`           double       DEFAULT 0.0,
--    `measurement_unit_code`          VARCHAR(255) DEFAULT '',
--    `min_best_before_date`           datetime     DEFAULT NULL,
--    `next_available_inflow_date`     datetime     DEFAULT NULL,
--    `next_available_inflow_quantity` double       DEFAULT 0.0,
--    `note`                           VARCHAR(255) DEFAULT '',
--    `origin_country`                 VARCHAR(255) DEFAULT '',
--    `purchase_price`                 double       DEFAULT 0.0,
--    `sort`                           BIGINT(20)   DEFAULT 0,
--    `taric`                          VARCHAR(255) DEFAULT '',
--    `is_batch`                       TINYINT(3)   DEFAULT 0,
--    `is_best_before`                 TINYINT(3)   DEFAULT 0,
--    `is_divisible`                   TINYINT(3)   DEFAULT 0,
--    `is_new_product`                 TINYINT(3)   DEFAULT 0,
--    `is_serial_number`               TINYINT(3)   DEFAULT 0,
--    `is_top_product`                 TINYINT(3)   DEFAULT 0,
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `product_translations`
(
    `model_id`              VARBINARY(32) NOT NULL,
    `language_iso`          VARCHAR(2)    NOT NULL,
    `delivery_status`       VARCHAR(255)  DEFAULT NULL,
    `description`           TEXT          DEFAULT NULL,
    `measurement_unit_name` VARCHAR(255)  DEFAULT NULL,
    `meta_description`      VARCHAR(255)  DEFAULT NULL,
    `meta_keywords`         VARCHAR(255)  DEFAULT NULL,
    `name`                  VARCHAR(255)  NOT NULL,
    `short_description`     TEXT          DEFAULT NULL,
    `title_tag`             VARCHAR(255)  DEFAULT NULL,
    `unit_name`             VARCHAR(255)  DEFAULT NULL,
    `url_path`              VARCHAR(255)  DEFAULT NULL,
    PRIMARY KEY (`model_id`, `language_iso`),
    FOREIGN KEY (`model_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);



CREATE TABLE IF NOT EXISTS `product_attrs`
(
    `id`                 VARBINARY(32)   NOT NULL,
    `product_id`         VARBINARY(32)   NOT NULL,
    `is_translated`      TINYINT         NOT NULL,
    `is_custom_property` TINYINT         NOT NULL,
    `type`               VARCHAR(8)      NOT NULL,
    PRIMARY KEY (`id`)
);


CREATE TABLE IF NOT EXISTS `product_attr_translations`
(
    `model_id`     VARBINARY(32) NOT NULL,
    `language_iso` VARCHAR(2)    NOT NULL,
    `name`         VARCHAR(255)  NOT NULL,
    `value`        TEXT          DEFAULT NULL,
    PRIMARY KEY (`model_id`, `language_iso`),
    FOREIGN KEY (`model_id`) REFERENCES `product_attrs` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);


CREATE TABLE IF NOT EXISTS `product_prices`
(
    `id`                VARBINARY(32) NOT NULL,
    `customer_group_id` VARBINARY(32) NOT NULL,
    -- `customer_id`       VARBINARY(32) NOT NULL,
    `product_id`        VARBINARY(32) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS `product_price_items`
(
    `product_price_id` VARBINARY(32) NOT NULL,
    `net_price`        DOUBLE NOT NULL,
    `quantity`         INT(),
    PRIMARY KEY (`product_price_id`, `quantity`),
    FOREIGN KEY (`product_price_id`) REFERENCES `product_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);


CREATE TABLE IF NOT EXISTS `product_special_prices`
(
    `id`                VARBINARY(32) NOT NULL,
    `product_id`        VARBINARY(32) NOT NULL,
    `active_from_date`  DATETIME DEFAULT NULL,
    `active_until_date` DATETIME DEFAULT NULL,
    `consider_date_limit`  TINYINT NOT NULL,
    `consider_stock_limit` TINYINT NOT NULL,
    `is_active`            TINYINT NOT NULL,
    `stock_limit`          INT DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS `product_special_price_items`
(
    `id`                       VARBINARY(32) NOT NULL,
    `product_special_price_id` VARBINARY(32) NOT NULL,
    `customer_group_id`        VARBINARY(32) NOT NULL,
    `price_net`                DOUBLE DEFAULT 0.0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`product_special_price_id`) REFERENCES `product_special_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

-- [x] product_prices
-- [x] product_price_items
-- [ ] product_special_prices
-- [ ] product_special_price_items
-- [ ] product_stock_levels
-- [ ] product_types
-- [ ] product_variations
-- [ ] product_variation_translations
-- [ ] product_variation_invisibilities
-- [ ] product_variation_values
-- [ ] product_variation_value_dependencies
-- [ ] product_variation_value_trnaslationss
