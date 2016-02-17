<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_QuickCreateFieldset extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/qcfieldset.phtml');
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
    public function getConfigurableAttributes()
    {
        return Mage::helper('udprod')->getConfigurableAttributes($this->getProduct(), !$this->getProduct()->getId());
    }
    public function getFirstAttribute()
    {
        $firstAttr = Mage::helper('udprod')->getCfgFirstAttribute($this->getProduct());
        if (!$firstAttr) {
            Mage::throwException('Options are not defined for this type of product');
        }
        return $firstAttr;
    }
    public function getFirstAttributes()
    {
        $firstAttr = Mage::helper('udprod')->getCfgFirstAttributes($this->getProduct());
        if (!$firstAttr) {
            Mage::throwException('Options are not defined for this type of product');
        }
        return $firstAttr;
    }
    public function getFirstAttributesValueTuples()
    {
        return Mage::helper('udprod')->getCfgFirstAttributesValueTuples($this->getProduct());
    }
    public function getFirstAttributeValues($used=null, $filters=array(), $filterFlag=true)
    {
        return $this->getAttributeValues($this->getFirstAttribute(), $used, $filters, $filterFlag);
    }
    public function getAttributeValues($attribute, $used=null, $filters=array(), $filterFlag=true)
    {
        return Mage::helper('udprod')->getCfgAttributeValues($this->getProduct(), $attribute, $used, $filters, $filterFlag);
    }

    public function renderQcPrices()
    {
        return Mage::app()->getLayout()->getBlockSingleton('udprod/vendor_product_qcPrices')
            ->setProduct($this->getProduct())
            ->setTemplate('unirgy/udprod/vendor/product/qcprices.phtml')
            ->setFieldName('product[__cfg_prices]')
            ->setParentBlock($this)
            ->toHtml();
    }
}