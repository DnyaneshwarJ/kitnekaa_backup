<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($installer->getTable('udropship/shipping'), 'vendor_ship_class', 'varchar(255)');
$conn->addColumn($installer->getTable('udropship/shipping'), 'customer_ship_class', 'varchar(255)');

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('udshipclass/customer')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('udshipclass/customer')}` (
  `class_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `country_id` varchar(2) NOT NULL,
  `region_id` int(11) NOT NULL,
  `postcode` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL,
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('udshipclass/vendor')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('udshipclass/vendor')}` (
  `class_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `country_id` varchar(2) NOT NULL,
  `region_id` int(11) NOT NULL,
  `postcode` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL,
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
