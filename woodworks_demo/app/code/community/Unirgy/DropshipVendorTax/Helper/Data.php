<?php

class Unirgy_DropshipVendorTax_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function processVendorChange($vendor)
    {
    }
    public function setVendorClassId($request, $item)
    {
        if ($item instanceof Unirgy_Dropship_Model_Vendor || is_scalar($item)) {
            $v = Mage::helper('udropship')->getVendor($item);
        } else {
            $v = Mage::helper('udropship')->getVendor($item->getUdropshipVendor());
        }
        if ($v->getId()) {
            $request->setVendorClassId($v->getVendorTaxClass());
            $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $request->getStore());
            if ($basedOn=='origin') {
                $request
                    ->setCountryId($v->getCountryId())
                    ->setRegionId($v->getRegionId())
                    ->setPostcode($v->getZip());
            }
        }
    }
}
