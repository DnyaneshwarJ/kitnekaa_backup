<?php

class Unirgy_Dropship_Model_Mysql4_Label_Batch_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/label_batch');
        parent::_construct();
    }
}