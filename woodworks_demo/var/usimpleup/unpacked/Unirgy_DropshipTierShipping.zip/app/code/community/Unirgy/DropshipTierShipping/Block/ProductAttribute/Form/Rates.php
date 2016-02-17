<?php


class Unirgy_DropshipTierShipping_Block_ProductAttribute_Form_Rates extends Varien_Data_Form_Element_Abstract
{
    public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udtiership/productAttribute_renderer_rates');
        return parent::getHtml();
    }
}