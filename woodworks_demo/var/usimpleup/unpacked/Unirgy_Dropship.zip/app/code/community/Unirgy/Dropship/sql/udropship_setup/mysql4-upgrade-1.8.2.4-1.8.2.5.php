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

if (Mage::helper('udropship')->isSalesFlat()) {
    $this->_conn->addColumn($this->getTable('sales_flat_shipment'), 'udropship_payout_status', 'varchar(50)');
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_grid'), 'udropship_payout_status', 'varchar(50)');
    $this->_conn->addKey($this->getTable('sales_flat_shipment_grid'), 'IDX_UDROPSHIP_PAYOUT_STATUS', 'udropship_payout_status');
} else {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $eav->addAttribute('shipment', 'udropship_payout_status', array('type' => 'varchar'));
}

$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'total_paid', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'total_due', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'notes', 'text');

$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'adjustment', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'my_adjustment', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_statement'), 'total_adjustment', 'decimal(12,4)');

$this->_conn->addColumn($this->getTable('udropship_vendor_statement_adjustment'), 'adjustment_prefix', 'varchar(64)');

$this->endSetup();
