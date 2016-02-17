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

$this->startSetup();

$this->run("

-- DROP TABLE IF EXISTS `{$this->getTable('udropship/vendor_lowstock')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship/vendor_lowstock')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `notified` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_VENDOR_PRODUCT` (`vendor_id`,`product_id`),
  KEY `IDX_PRODUCT_ID` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'notify_lowstock', 'tinyint');
$this->_conn->addKey($this->getTable('udropship_vendor'), 'IDX_NOTIFY_LOWSTOCK', 'notify_lowstock');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'notify_lowstock_qty', 'decimal(12,4)');

$this->endSetup();
