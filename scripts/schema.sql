CREATE TABLE IF NOT EXISTS `mappings`
(
    `endpoint` VARBINARY(32) NOT NULL,
    `host`     INT        NOT NULL,
    `type`     INT        NOT NULL,
    PRIMARY KEY (`endpoint`, `type`)
);


CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT current_timestamp(),
  `updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jtlId` int(11) NOT NULL,
  `sku` varchar(255) DEFAULT '',
  `stock` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT '',
  `vivino_name` varchar(255) DEFAULT '',
  `bottle_price` double DEFAULT NULL,
  `bottle_size` double DEFAULT NULL,
  `bottle_quantity` int(11) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `image` varchar(255) DEFAULT '',
  `ean` varchar(255) DEFAULT '',
  `wine_vintage` varchar(255) DEFAULT '',
  `wine_color` varchar(255) DEFAULT '',
  `country` varchar(255) DEFAULT '',
  `lbmZutaten` varchar(255) DEFAULT '',
  `alkohol` double DEFAULT NULL,
  `restzucker` double DEFAULT NULL,
  `lbm_brennwert_kj` double DEFAULT NULL,
  `allergen_sulfite` tinyint(1) DEFAULT NULL,
  `allergen_milch` tinyint(1) DEFAULT NULL,
  `allergen_ei` tinyint(1) DEFAULT NULL,
  `allergen_erdnuss` tinyint(1) DEFAULT NULL,
  `allergen_fisch` tinyint(1) DEFAULT NULL,
  `allergen_albumin` tinyint(1) DEFAULT NULL,
  `allergen_gluten` tinyint(1) DEFAULT NULL,
  `allergen_kasein` tinyint(1) DEFAULT NULL,
  `allergen_krebstier` tinyint(1) DEFAULT NULL,
  `allergen_lupinen` tinyint(1) DEFAULT NULL,
  `allergen_lysozym` tinyint(1) DEFAULT NULL,
  `allergen_nuss` tinyint(1) DEFAULT NULL,
  `allergen_sellerie` tinyint(1) DEFAULT NULL,
  `allergen_senf` tinyint(1) DEFAULT NULL,
  `allergen_sesam` tinyint(1) DEFAULT NULL,
  `allergen_soja` tinyint(1) DEFAULT NULL,
  `allergen_weichtier` tinyint(1) DEFAULT NULL,
  `allergen_farbstoffe` tinyint(1) DEFAULT NULL,
  `allergen_aromen` tinyint(1) DEFAULT NULL,
  `allergen_konservierungsstoffe` tinyint(1) DEFAULT NULL,
  `allergen_antioxidanzien` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `properties` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) DEFAULT NULL,
  `property_id` int(11) unsigned DEFAULT NULL,
  `value_name` varchar(255) DEFAULT NULL,
  `value_id` int(22) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `property_value` (`property_id`,`value_id`)
);

CREATE TABLE `weinsys_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE `weinsys_properties` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  `group_label` varchar(255) DEFAULT NULL,
  `value_name` varchar(255) DEFAULT NULL,
  `value_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
