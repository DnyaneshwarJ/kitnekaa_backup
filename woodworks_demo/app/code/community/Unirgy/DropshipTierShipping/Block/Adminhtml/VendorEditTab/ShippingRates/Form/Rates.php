<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_VendorEditTab_ShippingRates_Form_Rates extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_renderer_rates');
        return parent::getHtml();
    }
}