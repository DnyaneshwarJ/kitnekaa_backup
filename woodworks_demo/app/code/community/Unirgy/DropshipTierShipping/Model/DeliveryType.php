<?php

class Unirgy_DropshipTierShipping_Model_DeliveryType extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udtiership_delivery_type';
    protected $_eventObject = 'delivery_type';

    protected function _construct()
    {
        $this->_init('udtiership/deliveryType');
    }
}