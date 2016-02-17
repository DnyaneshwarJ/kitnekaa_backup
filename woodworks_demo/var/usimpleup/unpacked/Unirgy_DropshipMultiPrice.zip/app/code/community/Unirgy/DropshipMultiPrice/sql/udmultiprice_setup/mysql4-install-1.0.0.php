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

$conn->addColumn($installer->getTable('udropship/vendor_product'), 'vendor_title', 'varchar(255)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'vendor_price', 'decimal(12,4)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'state', 'varchar(32) NOT NULL DEFAULT \'new\'');

$conn->addColumn($installer->getTable('udropship/vendor_product'), 'special_price', 'decimal(12,4)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'special_from_date', 'datetime');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'special_to_date', 'datetime');

$conn->addColumn($installer->getTable('udropship/vendor_product'), 'state_descr', 'varchar(255)');
$conn->addColumn($installer->getTable('udropship/vendor_product'), 'freeshipping', 'tinyint(1)');

$conn->update($installer->getTable('udropship/vendor_product'), array('state'=>'new'), "state is null or state=''");

$installer->syncIndexTables();

$installer->endSetup();
