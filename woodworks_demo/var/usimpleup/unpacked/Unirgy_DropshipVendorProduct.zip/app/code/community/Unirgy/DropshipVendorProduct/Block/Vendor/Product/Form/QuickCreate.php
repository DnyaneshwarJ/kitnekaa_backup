<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Form_QuickCreate extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udprod/vendor_product_renderer_quickCreate');
        $this->_renderer->setProduct($this->_product);
        $this->_renderer->setCfgAttribute($this->getCfgAttribute());
        $this->_renderer->setCfgAttributeValue($this->getCfgAttributeValue());
        $this->_renderer->setCfgAttributeValueTuple($this->getCfgAttributeValueTuple());
        $this->_renderer->setCfgAttributeLabel($this->getCfgAttributeLabel());
        return parent::getHtml();
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