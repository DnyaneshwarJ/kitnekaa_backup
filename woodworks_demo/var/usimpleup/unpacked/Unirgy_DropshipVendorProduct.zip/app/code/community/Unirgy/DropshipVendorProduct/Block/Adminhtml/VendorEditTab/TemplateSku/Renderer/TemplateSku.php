<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_VendorEditTab_TemplateSku_Renderer_TemplateSku extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        $this->setTemplate('udprod/vendor/helper/template_sku_config.phtml');
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

    public function getTemplateSkus()
    {
        $value = $this->_element->getValue();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getGlobalTemplateSkuConfig()
    {
        $value = Mage::getStoreConfig('udprod/template_sku/value');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    public function getConfigurableAttributes($setId)
    {
        static $prod;
        if (null === $prod) {
            $prod = Mage::getModel('udprod/product')->setTypeId('configurable');
        }
        $prod->setAttributeSetId($setId);
        $_cfgAttributes = array();
        $cfgAttributes = $prod->getTypeInstance(true)
            ->getSetAttributes($prod);
        foreach ($cfgAttributes as $cfgAttribute) {
            if ($prod->getTypeInstance(true)->canUseAttribute($cfgAttribute, $prod)) {
                $_cfgAttributes[$cfgAttribute->getId()] = $cfgAttribute->getFrontend()->getLabel();
            }
        }
        return $_cfgAttributes;
    }

    public function getSetIds()
    {
        return Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
            ->load()
            ->toOptionHash();
    }

}