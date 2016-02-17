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

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_use_shipping', 'tinyint(1) NOT NULL default 1');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_email', 'varchar(127) NOT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_telephone', 'varchar(50) DEFAULT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_fax', 'varchar(50) DEFAULT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_vendor_attn', 'varchar(255) NOT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_street', 'varchar(255) NOT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_city', 'varchar(50) NOT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_zip', 'varchar(20) DEFAULT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_country_id', 'char(2) NOT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_region_id', 'mediumint(8) unsigned DEFAULT NULL');
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'billing_region', 'varchar(50) DEFAULT NULL');

$this->endSetup();