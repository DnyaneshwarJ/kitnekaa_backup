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

class Unirgy_Dropship_Model_Vendor_Product extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_product');
        parent::_construct();
    }

    public function getVendorCost()
    {
//        if (!$this->hasData('vendor_cost')) {
//            if ($this->getProductId()) {
//                $cost = Mage::getModel('catalog/product')->load($this->getProductId())->getCost();
//                $this->setData('vendor_cost', $cost);
//            }
//        }
        return $this->getData('vendor_cost');
    }
}