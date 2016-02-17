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

$this->_conn->addColumn($this->getTable('udropship/vendor'), 'allow_shipping_extra_charge', 'tinyint(1)');
$this->_conn->addColumn($this->getTable('udropship/vendor'), 'default_shipping_extra_charge_suffix', 'varchar(255)');
$this->_conn->addColumn($this->getTable('udropship/vendor'), 'default_shipping_extra_charge_type', 'varchar(32)');
$this->_conn->addColumn($this->getTable('udropship/vendor'), 'default_shipping_extra_charge', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship/vendor'), 'is_extra_charge_shipping_default', 'tinyint(1)');
$this->_conn->addColumn($this->getTable('udropship/vendor'), 'default_shipping_id', 'int(10)');

$this->_conn->addColumn($this->getTable('udropship/vendor_shipping'), 'allow_extra_charge', 'tinyint(1)');
$this->_conn->addColumn($this->getTable('udropship/vendor_shipping'), 'extra_charge_suffix', 'varchar(255)');
$this->_conn->addColumn($this->getTable('udropship/vendor_shipping'), 'extra_charge_type', 'varchar(32)');
$this->_conn->addColumn($this->getTable('udropship/vendor_shipping'), 'extra_charge', 'decimal(12,4)');

$this->endSetup();
