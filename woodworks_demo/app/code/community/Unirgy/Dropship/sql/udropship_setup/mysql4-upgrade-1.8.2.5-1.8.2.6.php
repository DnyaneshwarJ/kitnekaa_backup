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

-- DROP TABLE IF EXISTS `{$this->getTable('udropship_vendor_statement_row')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_statement_row')}` (
  `row_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `statement_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `po_id` int(10) unsigned DEFAULT NULL,
  `po_type` varchar(32) DEFAULT 'shipment',
  `order_increment_id` varchar(50) DEFAULT NULL,
  `po_increment_id` varchar(50) DEFAULT NULL,
  `order_created_at` datetime DEFAULT NULL,
  `po_created_at` datetime DEFAULT NULL,
  `has_error` tinyint(4) DEFAULT NULL,
  `subtotal` decimal(12,4) not null,
  `shipping` decimal(12,4) not null,
  `tax` decimal(12,4) not null,
  `handling` decimal(12,4) not null,
  `trans_fee` decimal(12,4) not null,
  `com_amount` decimal(12,4) not null,
  `adj_amount` decimal(12,4) not null,
  `total_payout` decimal(12,4) not null,
  `paid` tinyint,
  `error_info` text,
  `row_json` text,
  PRIMARY KEY (`row_id`),
  UNIQUE KEY `UNQ_PO_STATEMENT` (`po_id`,`po_type`,`statement_id`),
  KEY `FK_udropship_statement_row` (`statement_id`),
  CONSTRAINT `FK_udropship_vendor_statement_row` FOREIGN KEY (`statement_id`) REFERENCES `{$this->getTable('udropship_vendor_statement')}` (`vendor_statement_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'subtotal', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'shipping', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'tax', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'handling', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'trans_fee', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'com_amount', 'decimal(12,4)');

$tableName = $this->getTable('udropship_vendor_statement_adjustment');
if ($this->_conn->tableColumnExists($tableName, 'shipment_id')
    && !$this->_conn->tableColumnExists($tableName, 'po_id')
) {
    $this->_conn->changeColumn($tableName, 'shipment_id', 'po_id', "varchar(50) NOT NULL DEFAULT ''");
}

$this->_conn->addColumn($this->getTable('udropship_vendor_statement_adjustment'), 'po_type', "varchar(32) DEFAULT 'shipment'");
$this->_conn->dropKey($this->getTable('udropship_vendor_statement_adjustment'), 'IDX_SHIPMENT_ID');
$this->_conn->addKey($this->getTable('udropship_vendor_statement_adjustment'), 'IDX_PO_ID', array('po_id', 'po_type'));

$this->_conn->dropColumn($this->getTable('udropship_vendor_statement'), 'my_adjustment');
$this->_conn->dropColumn($this->getTable('udropship_vendor_statement'), 'adjustment');

$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'po_type', 'varchar(32)');

if (Mage::helper('udropship')->isSalesFlat()) {
    $this->_conn->addColumn($this->getTable('sales_flat_shipment'), 'statement_id', 'varchar(30)');
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_grid'), 'statement_id', 'varchar(30)');
    $this->_conn->addKey($this->getTable('sales_flat_shipment_grid'), 'IDX_UDROPSHIP_STATEMENT_ID', 'statement_id');
} else {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $eav->addAttribute('shipment', 'statement_id', array('type' => 'varchar'));
}

$this->endSetup();
