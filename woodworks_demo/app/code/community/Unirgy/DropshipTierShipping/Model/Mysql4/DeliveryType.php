<?php

class Unirgy_DropshipTierShipping_Model_Mysql4_DeliveryType extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_eventPrefix = 'udtiership_delivery_type_resource';

    protected function _construct()
    {
        $this->_init('udtiership/delivery_type', 'delivery_type_id');
    }

}
