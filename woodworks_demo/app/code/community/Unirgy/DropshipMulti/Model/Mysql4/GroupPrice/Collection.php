<?php

class Unirgy_DropshipMulti_Model_Mysql4_GroupPrice_Collection extends Unirgy_DropshipMulti_Model_Mysql4_GroupPrice_Abstract
{
    protected $_eventPrefix = 'udmulti_group_price_collection';
    protected $_eventObject = 'group_price_collection';

    protected function _construct()
    {
        $this->_init('udmulti/groupPrice');
    }
}