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
$conn->addColumn($this->getTable('udropship_vendor'), 'email_template', 'int(7) unsigned');

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$conn->addColumn($this->getTable('sales_order'), 'udropship_status', 'tinyint not null');
$conn->addKey($this->getTable('sales_order'), 'udropship_status', 'udropship_status', 'index');
$eav->addAttribute('order', 'udropship_status', array('type' => 'static'));

$eav->addAttribute('shipment', 'base_shipping_amount', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'shipping_amount', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'base_total_value', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'total_value', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'udropship_available_at', array('type' => 'datetime'));

$eav->addAttribute('shipment_track', 'value', array('type' => 'decimal'));
$eav->addAttribute('shipment_track', 'length', array('type' => 'decimal'));
$eav->addAttribute('shipment_track', 'width', array('type' => 'decimal'));
$eav->addAttribute('shipment_track', 'height', array('type' => 'decimal'));
$eav->addAttribute('shipment_track', 'result_extra', array('type' => 'text'));
$eav->addAttribute('shipment_track', 'pkg_num', array('type' => 'int'));
$eav->addAttribute('shipment_track', 'int_label_image', array('type' => 'text'));
$eav->addAttribute('shipment_track', 'label_render_options', array('type' => 'text'));

$eav->addAttribute('shipment_item', 'qty_shipped', array('type' => 'decimal'));

$this->endSetup();