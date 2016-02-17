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
 
$installer = $this;
$w = $this->_conn;
$installer->startSetup();

if (Mage::helper('udropship')->isSalesFlat()) {
    $table = $this->getTable('sales_flat_shipment');
    $w->modifyColumn($table, 'base_total_value', 'decimal(12,4)');
    $w->modifyColumn($table, 'total_value', 'decimal(12,4)');
    $w->modifyColumn($table, 'base_shipping_amount', 'decimal(12,4)');
    $w->modifyColumn($table, 'shipping_amount', 'decimal(12,4)');
    $w->modifyColumn($table, 'base_tax_amount', 'decimal(12,4)');
    $w->modifyColumn($table, 'total_cost', 'decimal(12,4)');
    $w->modifyColumn($table, 'transaction_fee', 'decimal(12,4)');
    $w->modifyColumn($table, 'commission_percent', 'decimal(12,4)');
    $w->modifyColumn($table, 'handling_fee', 'decimal(12,4)');
    
    $w->modifyColumn($this->getTable('sales_flat_shipment_item'), 'qty_shipped', 'decimal(12,4)');
    
    $table = $this->getTable('sales_flat_shipment_track');
    $w->modifyColumn($table, 'final_price', 'decimal(12,4)');
    $w->modifyColumn($table, 'value', 'decimal(12,4)');
    $w->modifyColumn($table, 'length', 'decimal(12,4)');
    $w->modifyColumn($table, 'width', 'decimal(12,4)');
    $w->modifyColumn($table, 'height', 'decimal(12,4)');
}

$this->endSetup();