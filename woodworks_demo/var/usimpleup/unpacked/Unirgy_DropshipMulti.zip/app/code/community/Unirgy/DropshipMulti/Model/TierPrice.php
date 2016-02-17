<?php

class Unirgy_DropshipMulti_Model_TierPrice extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udmulti_tier_price';
    protected $_eventObject = 'tier_price';

    protected function _construct()
    {
        $this->_init('udmulti/tierPrice');
    }
}
