<?php

class Unirgy_DropshipMulti_Model_Mysql4_TierPrice_Collection extends Unirgy_DropshipMulti_Model_Mysql4_GroupPrice_Abstract
{
    protected $_eventPrefix = 'udmulti_tier_price_collection';
    protected $_eventObject = 'tier_price_collection';

    protected function _construct()
    {
        $this->_init('udmulti/tierPrice');
    }
    protected function _beforeLoad()
    {
        $this->getSelect()->order('qty');
        parent::_beforeLoad();
        return $this;
    }

}