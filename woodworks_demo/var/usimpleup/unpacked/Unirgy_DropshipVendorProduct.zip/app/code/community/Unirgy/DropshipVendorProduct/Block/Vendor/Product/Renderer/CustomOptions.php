<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_CustomOptions extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _toHtml()
    {
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        $res = Mage_Core_Block_Template::_toHtml();
        Mage::helper('udropship/catalog')->setDesignStore();
        return $res;
    }
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
}