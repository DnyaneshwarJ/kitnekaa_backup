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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
class Unirgy_DropshipShippingClass_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const VENDOR_SHIP_CLASS_US = 1;
    const VENDOR_SHIP_CLASS_INT = 2;

    const CUSTOMER_SHIP_CLASS_US = 1;
    const CUSTOMER_SHIP_CLASS_INT = 2;

    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udshipclass');

        switch ($this->getPath()) {

        case 'vendor_ship_class':
            $options = Mage::getResourceSingleton('udshipclass/vendor_collection')->toOptionHash();
            $options[-1] = Mage::helper('udropship')->__('* Other Vendor');
            break;

        case 'customer_ship_class':
            $options = Mage::getResourceSingleton('udshipclass/customer_collection')->toOptionHash();
            $options[-1] = Mage::helper('udropship')->__('* Other Customer');
            break;

        default:
            Mage::throwException(Mage::helper('udropship')->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>Mage::helper('udropship')->__('* Please select')) + $options;
        }

        return $options;
    }
}