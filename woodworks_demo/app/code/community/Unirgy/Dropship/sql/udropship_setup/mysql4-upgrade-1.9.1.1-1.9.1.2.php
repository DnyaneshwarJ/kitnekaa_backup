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
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_track'), 'master_tracking_id', 'varchar(255)');
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_track'), 'package_count', 'tinyint');
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_track'), 'package_idx', 'tinyint');
} else {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $eav->addAttribute('shipment_track', 'master_tracking_id', array('type' => 'varchar'));
    $eav->addAttribute('shipment_track', 'package_count', array('type' => 'int'));
    $eav->addAttribute('shipment_track', 'package_idx', array('type' => 'int'));
}

$this->endSetup();
