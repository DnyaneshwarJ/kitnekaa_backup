<?php

class Unirgy_DropshipMulti_Model_Mysql4_TierPrice extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_eventPrefix = 'udmulti_tier_price_resource';

    protected function _construct()
    {
        $this->_init('udmulti/tier_price', 'value_id');
    }

}
