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
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$hlp = Mage::helper('udropship');

$this->startSetup();

$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'total_refund', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement_row'), 'discount', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales/shipment'), 'base_discount_amount', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales/shipment_grid'), 'base_discount_amount', 'decimal(12,4)');
$this->_conn->addKey($this->getTable('sales/shipment_grid'), 'IDX_BASE_DISCOUNT_AMOUNT', 'base_discount_amount');

$this->run("

-- DROP TABLE IF EXISTS `{$this->getTable('udropship_vendor_statement_refund_row')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_statement_refund_row')}` (
  `row_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `statement_id` int(10) unsigned NOT NULL,
  `refund_id` int(10) unsigned DEFAULT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `po_id` int(10) unsigned DEFAULT NULL,
  `po_type` varchar(32) DEFAULT 'shipment',
  `refund_increment_id` varchar(50) DEFAULT NULL,
  `order_increment_id` varchar(50) DEFAULT NULL,
  `po_increment_id` varchar(50) DEFAULT NULL,
  `refund_created_at` datetime DEFAULT NULL,
  `order_created_at` datetime DEFAULT NULL,
  `po_created_at` datetime DEFAULT NULL,
  `has_error` tinyint(4) DEFAULT NULL,
  `subtotal` decimal(12,4) not null,
  `shipping` decimal(12,4) not null,
  `tax` decimal(12,4) not null,
  `discount` decimal(12,4) not null,
  `com_amount` decimal(12,4) not null,
  `adj_amount` decimal(12,4) not null,
  `total_refund` decimal(12,4) not null,
  `error_info` text,
  `row_json` text,
  PRIMARY KEY (`row_id`),
  UNIQUE KEY `UNQ_PO_STATEMENT_RR` (`refund_id`,`po_id`,`po_type`,`statement_id`),
  KEY `FK_udropship_statement_refund_row` (`statement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$conn->addConstraint('FK_udropship_vendor_statement_refund_row', $this->getTable('udropship_vendor_statement_refund_row'), 'statement_id', $this->getTable('udropship_vendor_statement'), 'vendor_statement_id', 'CASCADE', 'CASCADE');

$this->endSetup();