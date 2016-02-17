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

$cEav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$cEav->updateAttribute('catalog_product', 'udropship_vendor', 'frontend_input', 'udropship_vendor');

$this->_conn->addKey($this->getTable('udropship/vendor'), 'IDX_VENDOR_NAME', 'vendor_name');

$this->endSetup();
