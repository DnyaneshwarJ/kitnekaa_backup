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

if (Mage::helper('udropship')->isSalesFlat()) {
    $conn->addColumn($this->getTable('sales_flat_shipment_comment'), 'is_vendor_notified', 'tinyint');
    $conn->addColumn($this->getTable('sales_flat_shipment_comment'), 'is_visible_to_vendor', 'tinyint default 1');
    $conn->addColumn($this->getTable('sales_flat_shipment_comment'), 'udropship_status', 'varchar(64)');
} else {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $eav->addAttribute('shipment_comment', 'is_vendor_notified', array('type' => 'int'));
    $eav->addAttribute('shipment_comment', 'is_visible_to_vendor', array('type' => 'int', 'default'=>1));
    $eav->addAttribute('shipment_comment', 'udropship_status', array('type' => 'varchar'));
}

$this->endSetup();