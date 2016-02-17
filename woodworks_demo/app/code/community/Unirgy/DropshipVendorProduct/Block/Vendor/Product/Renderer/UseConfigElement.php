<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_UseConfigElement extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;
    public function __construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/use_config_select.phtml');
    }
    public function getProduct()
    {
        return Mage::registry('product');
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

    public function getHtmlId($htmlId, $type=0)
    {
        $form = $this->_element->getForm();
        $elHtmlId = $this->_element->getData('html_id');
        if ($type===true) {
            $htmlId = $htmlId.$elHtmlId;
        } elseif ($type===false) {
            $htmlId = $elHtmlId.$htmlId;
        }
        return $form->getHtmlIdPrefix() . $htmlId . $form->getHtmlIdSuffix();
    }
    public function getName($name, $type=0)
    {
        $form = $this->_element->getForm();
        $elName = $this->_element->getData('name');
        if ($type===true) {
            $name = $name.$elName;
        } elseif ($type===false) {
            $name = $elName.$name;
        }
        if ($suffix = $form->getFieldNameSuffix()) {
            $name = $form->addSuffixToName($name, $suffix);
        }
        return $name;
    }
}
