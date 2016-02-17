<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Form_HandlingConfig extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udropship/adminhtml_vendor_helper_renderer_handlingConfig');
        return parent::getHtml();
    }
}