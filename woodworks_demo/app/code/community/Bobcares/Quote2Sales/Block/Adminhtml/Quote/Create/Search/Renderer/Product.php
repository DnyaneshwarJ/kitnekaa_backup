<?php

class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Search_Grid_Renderer_Product extends Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Product
{
    /**
     * Render product name to add Configure link
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $rendered       =  parent::render($row);
        $isConfigurable = $row->canConfigure();
        $style          = $isConfigurable ? '' : 'style="color: #CCC;"';
        $prodAttributes = $isConfigurable ? sprintf('list_type = "product_to_add" product_id = %s', $row->getId()) : 'disabled="disabled"';
        return sprintf('<a href="javascript:void(0)" %s class="f-right" %s>%s</a>',
            $style, $prodAttributes, Mage::helper('sales')->__('Configure')) . $rendered;
    }
}
