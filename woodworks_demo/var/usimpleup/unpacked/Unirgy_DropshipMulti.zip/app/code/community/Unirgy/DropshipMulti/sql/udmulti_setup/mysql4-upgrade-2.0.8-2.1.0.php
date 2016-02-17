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

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor_product'), 'backorders', 'tinyint(1) not null default 0');

$conn->addColumn($installer->getTable('udropship/vendor_product'), 'shipping_price', 'decimal(12,4)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'status', 'tinyint(1) not null');

$conn->update($installer->getTable('udropship/vendor_product'), array('status'=>1));

$conn->addColumn($installer->getTable('udropship/vendor_product'), 'reserved_qty', 'decimal(12,4)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'avail_state', 'varchar(32)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'avail_date', 'datetime');
$conn->addColumn($installer->getTable('udropship/vendor'), 'backorder_by_availability', 'tinyint(1)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'use_reserved_qty', 'tinyint(1)');

$conn->addColumn($installer->getTable('sales/shipment_item'), 'is_reserved', 'tinyint(1) default 1');

$installer->endSetup();
