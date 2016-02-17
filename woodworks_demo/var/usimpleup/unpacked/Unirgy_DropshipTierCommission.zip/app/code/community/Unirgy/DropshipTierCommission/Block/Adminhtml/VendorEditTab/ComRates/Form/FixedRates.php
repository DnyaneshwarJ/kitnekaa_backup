<?php

class Unirgy_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Form_FixedRates extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udtiercom/adminhtml_vendorEditTab_comRates_renderer_fixedRates');
        return parent::getHtml();
    }
}