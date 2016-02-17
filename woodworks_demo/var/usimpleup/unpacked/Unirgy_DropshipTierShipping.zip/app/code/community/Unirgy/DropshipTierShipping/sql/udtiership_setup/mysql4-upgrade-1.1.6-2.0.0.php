<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_delivery_type')}` (
  `delivery_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_code` varchar(64) DEFAULT NULL,
  `delivery_title` varchar(128) DEFAULT NULL,
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`delivery_type_id`),
  UNIQUE KEY `UNQ_DELIVERY_CODE` (`delivery_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_simple_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `customer_shipclass_id` text,
  `cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `additional` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `FK_TS_SIMPLE_DELIVERY_TYPE_ID` (`delivery_type_id`),
  CONSTRAINT `FK_TS_SIMPLE_DELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_vendor_simple_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `customer_shipclass_id` text,
  `cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `additional` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `FK_TS_VSIMPLE_DELIVERY_TYPE_ID` (`delivery_type_id`),
  KEY `FK_TS_SIMPLE_VENDOR` (`vendor_id`),
  CONSTRAINT `FK_TS_SIMPLE_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TS_VSIMPLE_DELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_simple_cond_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `customer_shipclass_id` text,
  `condition_name` varchar(128),
  `condition` text,
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `FK_TS_SIMPLE_COND_DELIVERY_TYPE_ID` (`delivery_type_id`),
  CONSTRAINT `FK_TS_SIMPLE_COND_DELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_vendor_simple_cond_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `customer_shipclass_id` text,
  `condition_name` varchar(128),
  `condition` text,
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `FK_TS_VSIMPLE_COND_DELIVERY_TYPE_ID` (`delivery_type_id`),
  KEY `FK_TS_SIMPLE_COND_VENDOR` (`vendor_id`),
  CONSTRAINT `FK_TS_SIMPLE_COND_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TS_VSIMPLE_COND_DELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `category_ids` text,
  `vendor_shipclass_id` text,
  `customer_shipclass_id` text,
  `cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `cost_extra` text,
  `max_cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `additional` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `additional_extra` text,
  `max_additional` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `handling` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `handling_extra` text,
  `max_handling` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `FK_TS_DELIVERY_TYPE_ID` (`delivery_type_id`),
  CONSTRAINT `FK_TS_DELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_vendor_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `category_ids` text,
  `customer_shipclass_id` text,
  `cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `cost_extra` text,
  `additional` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `additional_extra` text,
  `handling` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `handling_extra` text,
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `FK_TS_VDELIVERY_TYPE_ID` (`delivery_type_id`),
  KEY `FK_TS_VENDOR` (`vendor_id`),
  CONSTRAINT `FK_TS_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TS_VDELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

");

$conn->addColumn($this->getTable('udropship/vendor'), 'tiership_use_v2_rates', 'tinyint default 0');

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$eav->addAttribute('catalog_product', 'udtiership_use_custom', array(
    'type' => 'int', 'source' => 'eav/entity_attribute_source_boolean',
    'input'=>'select', 'label' => 'Use Product Specific Tier Shipping Rates',
    'input_renderer' => 'udtiership/productAttribute_form_useCustom',
    'user_defined' => 1, 'required' => 0, 'group'=>'Dropship Tier Shipping'
));

$eav->addAttribute('catalog_product', 'udtiership_rates', array(
    'type' => 'text', 'backend' => 'udtiership/productAttributeBackend_rates',
    'label' => 'Tier Shipping Rates','input_renderer' => 'udtiership/productAttribute_form_rates',
    'user_defined' => 1, 'required' => 0, 'group'=>'Dropship Tier Shipping'
));

$installer->endSetup();
