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
$conn->addKey($this->getTable('udropship_vendor'), 'IDX_STATUS', 'status');

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$eav->updateAttribute('catalog_product', 'udropship_vendor', 'backend_model', 'Unirgy_Dropship_Model_Mysql4_Vendor_Backend');
$eav->updateAttribute('catalog_product', 'udropship_vendor', 'source_model', 'Unirgy_Dropship_Model_Vendor_Source');

if (version_compare(Mage::getVersion(), '1.3.0', '>=')) {
    $eav->updateAttribute('catalog_product', 'udropship_vendor', 'used_in_product_listing', 1);
}

$eav->addAttribute('quote', 'udropship_shipping_details', array('type' => 'static'));
$conn->addColumn($this->getTable('sales_flat_quote'), 'udropship_shipping_details', 'text');

$eav->addAttribute('order', 'udropship_shipping_details', array('type' => 'text'));

$this->endSetup();