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
class Unirgy_DropshipTierShipping_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const CM_MAX_FIRST_ADDITIONAL = 1;
    const CM_SUM_FIRST_ADDITIONAL = 2;
    const CM_MULTIPLY_FIRST       = 3;
    const CM_MAX_FIRST = 4;
    const CM_SUM_FIRST = 5;

    const CT_SEPARATE = 1;
    const CT_BASE_PLUS_ZONE_PERCENT = 2;
    const CT_BASE_PLUS_ZONE_FIXED   = 3;

    const FL_VENDOR_BASE = 1;
    const FL_VENDOR_DEFAULT = 2;
    const FL_TIER = 2;

    const USE_RATES_V1 = 0;
    const USE_RATES_V1_SIMPLE = 1;
    const USE_RATES_V2 = 2;
    const USE_RATES_V2_SIMPLE = 3;
    const USE_RATES_V2_SIMPLE_COND = 4;

    const SIMPLE_COND_FULLWEIGHT = 'full_weight';
    const SIMPLE_COND_SUBTOTAL = 'subtotal';
    const SIMPLE_COND_TOTALQTY = 'total_qty';

    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udtiership');

        switch ($this->getPath()) {

        case 'carriers/udtiership/additional_calculation_type':
        case 'carriers/udtiership/cost_calculation_type':
        case 'carriers/udtiership/handling_calculation_type':
            $options = array(
                self::CT_SEPARATE => Mage::helper('udropship')->__('Separate per customer shipclass'),
                self::CT_BASE_PLUS_ZONE_PERCENT => Mage::helper('udropship')->__('Base plus percent per customer shipclass'),
                self::CT_BASE_PLUS_ZONE_FIXED   => Mage::helper('udropship')->__('Base plus fixed per customer shipclass'),
            );
            break;
        case 'carriers/udtiership/calculation_method':
            $options = array(
                self::CM_MAX_FIRST_ADDITIONAL => Mage::helper('udropship')->__('Max first item other additional'),
                self::CM_MAX_FIRST => Mage::helper('udropship')->__('Max first item (discard qty)'),
                self::CM_SUM_FIRST_ADDITIONAL => Mage::helper('udropship')->__('Sum first item other additional'),
                self::CM_SUM_FIRST => Mage::helper('udropship')->__('Sum first item (discard qty)'),
                self::CM_MULTIPLY_FIRST       => Mage::helper('udropship')->__('Multiply first item (additional not used)'),
            );
            break;

        case 'carriers/udtiership/fallback_lookup':
            $options = array(
                self::FL_VENDOR_BASE => Mage::helper('udropship')->__('Vendor up to BASE'),
                self::FL_VENDOR_DEFAULT => Mage::helper('udropship')->__('Vendor up to DEFAULT'),
                self::FL_TIER => Mage::helper('udropship')->__('Vendor/Global by tier'),
            );
            break;

        case 'carriers/udtiership/handling_apply_method':
            $options = array(
                'none'      => 'None',
                'fixed'     => 'Fixed Per Category',
                'fixed_max' => 'Max Fixed',
                'percent'   => 'Percent',
            );
            break;

        case 'carriers/udtiership/use_simple_rates':
           $options = array(
               self::USE_RATES_V1 => Mage::helper('udropship')->__('V1 Rates'),
               self::USE_RATES_V1_SIMPLE => Mage::helper('udropship')->__('V1 Simple Rates'),
               self::USE_RATES_V2 => Mage::helper('udropship')->__('V2 By Category/VendorClass First/Additional/Handling Rates'),
               self::USE_RATES_V2_SIMPLE => Mage::helper('udropship')->__('V2 Simple First/Additional Rates'),
               self::USE_RATES_V2_SIMPLE_COND => Mage::helper('udropship')->__('V2 Simple Conditional Rates'),
           );
           break;

        case 'simple_condition':
            $options = array(
                self::SIMPLE_COND_FULLWEIGHT => Mage::helper('udropship')->__('Full Weight'),
                self::SIMPLE_COND_SUBTOTAL => Mage::helper('udropship')->__('Subtotal'),
                self::SIMPLE_COND_TOTALQTY => Mage::helper('udropship')->__('Total Qty'),
            );
            break;

        case 'tiership_delivery_type_selector':
        case 'carriers/udtiership/delivery_type_selector':
            $selector = true;
            $options = Mage::getResourceModel('udtiership/deliveryType_collection')->toOptionHash();
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