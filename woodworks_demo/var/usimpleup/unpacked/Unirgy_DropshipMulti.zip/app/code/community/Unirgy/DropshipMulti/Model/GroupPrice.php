<?php

class Unirgy_DropshipMulti_Model_GroupPrice extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udmulti_group_price';
    protected $_eventObject = 'group_price';

    protected function _construct()
    {
        $this->_init('udmulti/groupPrice');
    }
}