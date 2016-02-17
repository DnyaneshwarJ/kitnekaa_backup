<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$hlp = Mage::helper('udropship');

$this->startSetup();

$installer = $this;

$conn = $this->_conn;

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('udropship/vendor_product_assoc')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('udropship/vendor_product_assoc')}` (
  `vendor_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `is_attribute` tinyint(1) unsigned NOT NULL,
  `is_udmulti` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `UNQ_UDVP_ASSOC_VIDPID` (`vendor_id`,`product_id`),
  KEY `UNQ_UDVP_ASSOC_VID` (`vendor_id`),
  KEY `UNQ_UDVP_ASSOC_PID` (`product_id`),
  CONSTRAINT `FK_UNQ_UDVP_ASSOC_VID` FOREIGN KEY (`vendor_id`) REFERENCES `{$installer->getTable('udropship/vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `UNQ_UDVP_ASSOC_PID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

try {
Mage::getSingleton('index/indexer')->getProcessByCode('udropship_vendor_product_assoc')->reindexEverything();
}catch (Exception $e) {}

$this->endSetup();