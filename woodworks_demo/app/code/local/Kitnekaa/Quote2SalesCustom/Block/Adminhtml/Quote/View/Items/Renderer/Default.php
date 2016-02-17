<?php
class Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Quote_View_Items_Renderer_Default extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Items_Renderer_Default
{
    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br />')
    {
        return  Mage::helper('adminhtml/sales')->displayPrices($this->getPriceDataObject(), $basePrice, $price, $strong, $separator);
    }
}
