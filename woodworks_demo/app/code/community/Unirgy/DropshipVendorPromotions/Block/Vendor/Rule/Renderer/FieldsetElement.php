<?php

class Unirgy_DropshipVendorPromotions_Block_Vendor_Rule_Renderer_FieldsetElement extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('unirgy/udpromo/vendor/rule/renderer/fieldset_element.phtml');
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
        //if ($this->_element->getSwitchAdminhtml()) Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        $html = $this->_element->getElementHtml();
        //if ($this->_element->getSwitchAdminhtml()) Mage::helper('udropship/catalog')->setDesignStore();
        return $html;
    }
}