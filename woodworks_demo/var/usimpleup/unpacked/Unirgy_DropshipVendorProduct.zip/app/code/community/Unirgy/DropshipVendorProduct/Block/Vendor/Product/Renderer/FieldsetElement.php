<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_FieldsetElement extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/fieldset_element.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        $element->addClass('udvalidate-'.$element->getId());
        return $this->toHtml();
    }

    public function getElementHtml()
    {
        //Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        $html = $this->_element->getElementHtml();
        //Mage::helper('udropship/catalog')->setDesignStore();
        return $html;
    }

    public function getProduct()
    {
        return Mage::registry('current_product') ? Mage::registry('current_product') : Mage::registry('product');
    }

}