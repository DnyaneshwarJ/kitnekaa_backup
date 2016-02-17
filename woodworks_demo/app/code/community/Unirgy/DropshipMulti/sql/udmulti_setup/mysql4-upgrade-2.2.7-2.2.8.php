<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('udmulti/tier_price')}` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vendor_id` int(11) unsigned NOT NULL DEFAULT '0',
  `all_groups` smallint(5) unsigned NOT NULL DEFAULT '1',
  `customer_group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qty` decimal(12,4) NOT NULL DEFAULT '1.0000',
  `value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_UVP_TIERPRICE` (`product_id`,`vendor_id`,`all_groups`,`customer_group_id`,`qty`,`website_id`),
  KEY `IDX_UVP_TIERPRICE_PRODUCT_ID` (`product_id`),
  KEY `IDX_UVP_TIERPRICE_VENDOR_ID` (`vendor_id`),
  KEY `IDX_UVP_TIERPRICE_CUSTOMER_GROUP_ID` (`customer_group_id`),
  KEY `IDX_UVP_TIERPRICE_WEBSITE_ID` (`website_id`),
  CONSTRAINT `FK_UVP_TIERPRICE_VP_ID` FOREIGN KEY (`vendor_product_id`) REFERENCES `{$this->getTable('udropship_vendor_product')}` (`vendor_product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_TIERPRICE_GROUP_ID` FOREIGN KEY (`customer_group_id`) REFERENCES `{$this->getTable('customer_group')}` (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_TIERPRICE_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_TIERPRICE_VENDOR_ID` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_TIERPRICE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$this->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('udmulti/group_price')}` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vendor_id` int(10) unsigned NOT NULL DEFAULT '0',
  `all_groups` smallint(5) unsigned NOT NULL DEFAULT '1',
  `customer_group_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_UVP_GROUPPRICE` (`product_id`,`vendor_id`,`all_groups`,`customer_group_id`,`website_id`),
  KEY `IDX_UVP_GROUPPRICE_PRODUCT_ID` (`product_id`),
  KEY `IDX_UVP_GROUPPRICE_VENDOR_ID` (`vendor_id`),
  KEY `IDX_UVP_GROUPPRICE_CUSTOMER_GROUP_ID` (`customer_group_id`),
  KEY `IDX_UVP_GROUPPRICE_WEBSITE_ID` (`website_id`),
  CONSTRAINT `FK_UVP_GROUPPRICE_VP_ID` FOREIGN KEY (`vendor_product_id`) REFERENCES `{$this->getTable('udropship_vendor_product')}` (`vendor_product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_GROUPPRICE_GROUP_ID` FOREIGN KEY (`customer_group_id`) REFERENCES `{$this->getTable('customer_group')}` (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_GROUPPRICE_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_GROUPPRICE_VENDOR_ID` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UVP_GROUPPRICE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$this->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();