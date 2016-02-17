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
 * @package    Unirgy_DropshipVendorTax
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
class Unirgy_DropshipVendorTax_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const TAX_CLASS_TYPE_VENDOR = 'VENDOR';
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udtax');

        switch ($this->getPath()) {

        case 'vendor_tax_class':
            $options = Mage::getResourceModel('tax/class_collection')
                ->addFieldToFilter('class_type', Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR)
                ->load()
                ->toOptionHash();

            $options = array('0' => Mage::helper('udropship')->__('None')) + $options;
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