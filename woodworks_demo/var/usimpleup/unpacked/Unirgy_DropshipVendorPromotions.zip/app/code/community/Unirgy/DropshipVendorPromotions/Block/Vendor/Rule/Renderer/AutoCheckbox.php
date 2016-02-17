<?php

class Unirgy_DropshipVendorPromotions_Block_Vendor_Rule_Renderer_AutoCheckbox
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Checkbox render function
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $checkbox = new Varien_Data_Form_Element_Checkbox($element->getData());
        $checkbox->setForm($element->getForm());

        $elementHtml = $checkbox->getElementHtml() . sprintf(
                '<label for="%s"><b>%s</b></label><p class="note">%s</p>&nbsp;',
                $element->getHtmlId(), $element->getLabel(), $element->getNote()
            );
        $html  = $elementHtml;

        return $html;
    }

}
