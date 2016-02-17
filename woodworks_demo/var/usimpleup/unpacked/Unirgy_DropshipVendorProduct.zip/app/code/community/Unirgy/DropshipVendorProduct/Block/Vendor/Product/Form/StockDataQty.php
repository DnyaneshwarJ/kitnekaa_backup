<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Form_StockDataQty extends Varien_Data_Form_Element_Text
{
    public function getAfterElementHtml()
    {
        $name = $this->getData('name');
        $name = str_replace('qty', 'original_inventory_qty', $name);
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        $html = sprintf('<input name="%s" type="hidden" value="%s" />', $name, $this->getEscapedValue());

        $html .= parent::getAfterElementHtml();
        return $html;
    }
}