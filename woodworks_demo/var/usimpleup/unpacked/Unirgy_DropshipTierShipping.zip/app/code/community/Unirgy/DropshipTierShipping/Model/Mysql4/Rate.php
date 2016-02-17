<?php

class Unirgy_DropshipTierShipping_Model_Mysql4_Rate extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
    }

    public function useRateSetup($type, $isVendor=false, $isProduct=false)
    {
        if ($isProduct) {
            $this->_init('udtiership/product_rates', 'rate_id');
        } else {
        switch ($type) {
            case Unirgy_DropshipTierShipping_Model_Source::USE_RATES_V2:
                if ($isVendor) {
                    $this->_init('udtiership/vendor_rates', 'rate_id');
                } else {
                    $this->_init('udtiership/rates', 'rate_id');
                }
                break;
            case Unirgy_DropshipTierShipping_Model_Source::USE_RATES_V2_SIMPLE:
                if ($isVendor) {
                    $this->_init('udtiership/vendor_simple_rates', 'rate_id');
                } else {
                    $this->_init('udtiership/simple_rates', 'rate_id');
                }
                break;
            case Unirgy_DropshipTierShipping_Model_Source::USE_RATES_V2_SIMPLE_COND:
                if ($isVendor) {
                    $this->_init('udtiership/vendor_simple_cond_rates', 'rate_id');
                } else {
                    $this->_init('udtiership/simple_cond_rates', 'rate_id');
                }
                break;
        }
        }
    }


}
