<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_GroupedAssocProductsFieldset extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/grouped_assoc_products_fieldset.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }
    public function getChildElementHtml($elem='_cfg_quick_create')
    {
        return $this->getElement()->getForm()->getElement($elem)->toHtml();
    }
    public function getChildElement($elem='_cfg_quick_create')
    {
        return $this->getElement()->getForm()->getElement($elem);
    }
    protected $_product;
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }
    public function getProduct()
    {
        return $this->_product;
    }
}