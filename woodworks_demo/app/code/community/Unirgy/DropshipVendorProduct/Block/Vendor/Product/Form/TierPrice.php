<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Form_TierPrice extends Varien_Data_Form_Element_Text
{
    public function getElementHtml()
    {
        //Mage::helper('udropship/catalog')->setDesignStore();
        $html = Mage::app()->getLayout()->createBlock('udprod/vendor_product_renderer_tierPrice')->render($this);
        //Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        return $html;
    }
}