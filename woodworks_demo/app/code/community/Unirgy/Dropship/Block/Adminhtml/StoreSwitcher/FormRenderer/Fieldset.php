<?php

class Unirgy_Dropship_Block_Adminhtml_StoreSwitcher_FormRenderer_Fieldset
    extends Mage_Adminhtml_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;
    protected function _construct()
    {
        $this->setTemplate('udropship/store_switcher/form_renderer/fieldset.phtml');
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

    public function getHintHtml()
    {
        return '';
    }
}
