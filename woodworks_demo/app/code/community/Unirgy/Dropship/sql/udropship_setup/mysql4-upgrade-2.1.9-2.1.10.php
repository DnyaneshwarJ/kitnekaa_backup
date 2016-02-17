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

-- DROP TABLE IF EXISTS `{$installer->getTable('udropship/shipping_title')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('udropship/shipping_title')}` (
  `title_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shipping_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`title_id`),
  UNIQUE KEY `UNQ_UDSHIP_TITLE_SHIP_ID_STORE_ID` (`shipping_id`,`store_id`),
  KEY `IDX_UDSHIP_TITLE_STORE_ID` (`store_id`),
  KEY `IDX_UDSHIP_TITLE_SHIP_ID` (`shipping_id`),
  CONSTRAINT `FK_UDSHIP_TITLE_PARENT` FOREIGN KEY (`shipping_id`) REFERENCES `{$installer->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UDSHIP_TITLE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();