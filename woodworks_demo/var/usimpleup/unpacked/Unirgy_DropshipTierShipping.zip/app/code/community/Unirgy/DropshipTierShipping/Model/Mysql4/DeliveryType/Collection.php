<?php

class Unirgy_DropshipTierShipping_Model_Mysql4_DeliveryType_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_eventPrefix = 'udtiership_delivery_type_collection';
    protected $_eventObject = 'delivery_type_collection';

    protected function _construct()
    {
        $this->_init('udtiership/deliveryType');
    }

    public function setDeliverySort($dir='ASC')
    {
        $this->setOrder('main_table.sort_order', $dir);
        return $this;
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('delivery_type_id', 'delivery_title');
    }
}