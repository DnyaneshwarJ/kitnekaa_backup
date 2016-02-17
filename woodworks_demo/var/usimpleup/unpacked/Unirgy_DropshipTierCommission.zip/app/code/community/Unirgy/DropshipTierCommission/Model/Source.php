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
class Unirgy_DropshipTierCommission_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udtiercom');

        switch ($this->getPath()) {

        case 'udropship/tiercom/fixed_rule':
        case 'tiercom_fixed_rates':
            $options = array(
                'item_price' => Mage::helper('udropship')->__('Item Price')
            );
            if ($this->getPath()=='tiercom_fixed_rates') {
                $options = array('' => Mage::helper('udropship')->__('* Use Global Config')) + $options;
            }
            break;

        case 'udropship/tiercom/fallback_lookup':
        case 'tiercom_fallback_lookup':
            $options = array(
                'vendor' => Mage::helper('udropship')->__('Vendor First'),
                'tier' => Mage::helper('udropship')->__('Tier First')
            );
            if ($this->getPath()=='tiercom_fallback_lookup') {
                $options = array('-1' => Mage::helper('udropship')->__('* Use Global Config')) + $options;
            }
            break;

        case 'udropship/tiercom/fixed_calculation_type':
        case 'tiercom_fixed_calc_type':
            $options = array(
                'flat' => Mage::helper('udropship')->__('Flat (per po)'),
                'tier' => Mage::helper('udropship')->__('Tier (per item)'),
                'rule' => Mage::helper('udropship')->__('Rule Based (per item)'),
                'flat_rule' => Mage::helper('udropship')->__('Tier + Rule Based'),
                'flat_tier' => Mage::helper('udropship')->__('Flat + Tier'),
                'flat_rule' => Mage::helper('udropship')->__('Flat + Rule Based'),
                'flat_tier_rule' => Mage::helper('udropship')->__('Flat + Tier + Rule Based'),
            );
            if ($this->getPath()=='tiercom_fixed_calc_type') {
                $options = array('' => Mage::helper('udropship')->__('* Use Global Config')) + $options;
            }
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