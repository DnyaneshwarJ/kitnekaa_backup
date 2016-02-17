<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Form_Price extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Price
{
    public function getEscapedValue($index=null)
    {
        $value = $this->getValue();

        if (substr($value, 0, 1)=='$') {
            return $value;
        } elseif (!is_numeric($value)) {
            return null;
        }

        return number_format($value, 2, null, '');
    }
}