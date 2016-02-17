<?php

class Unirgy_DropshipTierCommission_Block_Vendor_Rates extends Mage_Core_Block_Template
{
    public function getTopCategories()
    {
        return Mage::helper('udtiercom')->getTopCategories();
    }

    public function getTiercomRates()
    {
        $value = $this->getVendor()->getTiercomRates();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getGlobalTierComConfig()
    {
        $value = Mage::getStoreConfig('udropship/tiercom/rates');
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
}