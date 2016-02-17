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

class Unirgy_Dropship_Model_RateResult extends Mage_Shipping_Model_Rate_Result
{
    public function sortRatesByPriority ()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        foreach ($this->_rates as $i => $rate) {
            $cmpPrice = $rate->hasBeforeExtPrice() ? $rate->getBeforeExtPrice() : $rate->getPrice();
            $tmp[$i] = 100*$rate->getPriority()+$cmpPrice+(int)$rate->getIsExtraCharge();
        }

        natsort($tmp);

        foreach ($tmp as $i => $price) {
            $result[] = $this->_rates[$i];
        }

        $this->reset();
        $this->_rates = $result;
        return $this;
    }
    public function sortRatesByPrice()
    {
        if (Mage::getStoreConfigFlag('udropship/customer/allow_shipping_extra_charge')) {
            $this->sortRatesByPriority();
        } else {
            parent::sortRatesByPrice();
        }
        return $this;
    }
}