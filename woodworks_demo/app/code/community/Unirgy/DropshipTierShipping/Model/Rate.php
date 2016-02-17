<?php

class Unirgy_DropshipTierShipping_Model_Rate extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udtiership/rate');
        $this->getResource()->useRateSetup($this->getData('__use_rate_setup'), $this->getData('__use_vendor'), $this->getData('__use_product'));
    }

}