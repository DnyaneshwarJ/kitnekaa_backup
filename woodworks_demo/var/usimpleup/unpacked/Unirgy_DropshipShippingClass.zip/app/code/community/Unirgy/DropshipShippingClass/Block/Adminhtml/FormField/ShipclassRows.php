<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_FormField_ShipclassRows extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udshipclass/adminhtml_formRenderer_shipclassRows');
        return parent::getHtml();
    }
}