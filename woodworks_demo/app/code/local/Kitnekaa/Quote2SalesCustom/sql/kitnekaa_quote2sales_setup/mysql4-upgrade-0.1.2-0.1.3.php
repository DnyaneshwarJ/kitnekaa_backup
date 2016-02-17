<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('quote2sales_requests_products')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('quote2sales_requests_products')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(11) unsigned DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `qty` DECIMAL(10, 2) DEFAULT 0,
  `target_price` DECIMAL(10, 2) DEFAULT 0,
  `frequency` varchar(200) DEFAULT NULL,
  `when_need` varchar(200) DEFAULT NULL,
  `comment` text,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;
");

$installer->endSetup();