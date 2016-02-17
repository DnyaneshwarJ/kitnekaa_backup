<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_VendorEditTab_TemplateSku_Form_TemplateSku extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udprod/adminhtml_vendorEditTab_templateSku_renderer_templateSku');
        return parent::getHtml();
    }
}