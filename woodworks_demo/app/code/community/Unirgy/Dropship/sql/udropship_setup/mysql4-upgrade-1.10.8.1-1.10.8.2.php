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

$cEav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$cEav->addAttribute('catalog_product', 'udropship_calculate_rates', array(
    'type' => 'int', 'source' => 'udropship/productAttributeSource_calculateRates',
    'input'=>'select', 'label' => 'Dropship Rates Calculation Type',
    'user_defined' => 1, 'required' => 0, 'group'=>'General'
));

$this->endSetup();
