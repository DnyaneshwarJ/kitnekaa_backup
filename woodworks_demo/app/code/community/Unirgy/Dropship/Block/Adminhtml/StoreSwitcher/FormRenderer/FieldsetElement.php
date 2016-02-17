<?php

class Unirgy_Dropship_Block_Adminhtml_StoreSwitcher_FormRenderer_FieldsetElement
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;
    protected function _construct()
    {
        $this->setTemplate('udropship/store_switcher/form_renderer/fieldset_element.phtml');
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
