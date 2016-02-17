<?php

class Unirgy_DropshipTierShipping_Block_Vendor_SimpleRates extends Mage_Core_Block_Template
{
    public function getTiershipSimpleRates()
    {
        $value = $this->getVendor()->getTiershipSimpleRates();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getGlobalTierShipConfigSimple()
    {
        $value = Mage::getStoreConfig('carriers/udtiership/simple_rates');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }

    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function getColumnTitle($subkeyColumns, $idx)
    {
        reset($subkeyColumns);
        $i=0; while ($i++!=$idx) next($subkeyColumns);
        $title = '';
        $column = current($subkeyColumns);
        switch ($column[1]) {
            case 'cost':
                $title = Mage::helper('udropship')->__('Cost for the first item');
                break;
            case 'additional':
                $title = Mage::helper('udropship')->__('Additional item cost');
                break;
        }
        return $title;
    }

}