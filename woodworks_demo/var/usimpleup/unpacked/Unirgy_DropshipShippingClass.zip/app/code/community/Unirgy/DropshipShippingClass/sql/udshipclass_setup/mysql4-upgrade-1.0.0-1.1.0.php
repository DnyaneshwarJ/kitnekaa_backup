<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($installer->getTable('udropship/shipping'), 'vendor_ship_class', 'varchar(255)');
$conn->addColumn($installer->getTable('udropship/shipping'), 'customer_ship_class', 'varchar(255)');

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('udshipclass/customer_row')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('udshipclass/customer_row')}` (
  `class_id` smallint(6) NOT NULL,
  `country_id` varchar(2) NOT NULL,
  `region_id` text NOT NULL,
  `postcode` text DEFAULT NULL,
  KEY `FK_UDSC_CUSTOMER_CLASS_ID` (`class_id`),
  CONSTRAINT `FK_UDSC_CUSTOMER_CLASS_ID` FOREIGN KEY (`class_id`) REFERENCES `{$installer->getTable('udshipclass/customer')}` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `UNQ_CLASS_COUNTRY` (`class_id`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$installer->getTable('udshipclass/vendor_row')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('udshipclass/vendor_row')}` (
  `class_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `country_id` varchar(2) NOT NULL,
  `region_id` text NOT NULL,
  `postcode` text DEFAULT NULL,
  KEY `FK_UDSC_VENDOR_CLASS_ID` (`class_id`),
  CONSTRAINT `FK_UDSC_VENDOR_CLASS_ID` FOREIGN KEY (`class_id`) REFERENCES `{$installer->getTable('udshipclass/vendor')}` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `UNQ_CLASS_COUNTRY` (`class_id`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
  insert ignore into {$installer->getTable('udshipclass/customer_row')} select class_id, country_id, region_id, postcode from {$installer->getTable('udshipclass/customer')};
  insert ignore into {$installer->getTable('udshipclass/vendor_row')} select class_id, country_id, region_id, postcode from {$installer->getTable('udshipclass/vendor')};
");

$installer->endSetup();
