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

$conn = $this->_conn;

if (!Mage::helper('udropship')->isSalesFlat()) {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $eav->addAttribute('shipment_item', 'vendor_sku', array('type' => 'varchar'));
    $eav->addAttribute('shipment_item', 'vendor_simple_sku', array('type' => 'varchar'));
} else {
    $conn->addColumn($this->getTable('sales/shipment_item'), 'vendor_sku', 'varchar(255)');
    $conn->addColumn($this->getTable('sales/shipment_item'), 'vendor_simple_sku', 'varchar(255)');
}

$this->endSetup();
