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

$hlp = Mage::helper('udropship');

$this->startSetup();

$this->_conn->addColumn($this->getTable('sales/shipment'), 'base_shipping_tax', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales/shipment'), 'shipping_tax', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales/shipment'), 'base_shipping_amount_incl', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales/shipment'), 'shipping_amount_incl', 'decimal(12,4)');

$this->endSetup();