<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Wysiwyg extends Varien_Data_Form_Element_Textarea
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        if ($this->isWysiwygAllowed()) {
            $html .= '<br />'
            . Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('udropship')->__('WYSIWYG Editor'),
                    'type'    => 'button',
                    'disabled' => false,
                    'class' => 'form-button',
                        'onclick' => 'uVendorWysiwygEditor.open(\''.Mage::helper('udropship')->getVendorPortalJsUrl('*/*/wysiwyg').'\', \''.$this->getHtmlId().'\')'
                ))->toHtml();
        }
        return $html;
    }

    public function isWysiwygAllowed()
    {
        if (Mage::helper('udropship')->isWysiwygAllowed()
            && Mage::helper('catalog')->isModuleEnabled('Mage_Cms')
            && (bool)Mage::getSingleton('cms/wysiwyg_config')->isEnabled()
            && $this->getProductAttribute()
        ) {
            return $this->getProductAttribute()->getIsWysiwygEnabled();
        }
        return false;
    }
}