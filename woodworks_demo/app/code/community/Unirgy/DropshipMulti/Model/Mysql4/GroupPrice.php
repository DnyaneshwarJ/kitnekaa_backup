<?php

class Unirgy_DropshipMulti_Model_Mysql4_GroupPrice extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_eventPrefix = 'udmulti_group_price_resource';

    protected function _construct()
    {
        $this->_init('udmulti/group_price', 'value_id');
    }

}
