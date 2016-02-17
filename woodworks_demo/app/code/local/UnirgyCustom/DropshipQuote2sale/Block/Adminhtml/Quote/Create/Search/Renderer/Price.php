<?php

class UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Quote_Create_Search_Renderer_Price extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    /**
     * Render minimal price for downloadable products
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        if ($row->getTypeId() == 'downloadable') {
            $row->setPrice($row->getPrice());
        }
        if (Mage::helper('udquote2sale')->getVendorId()) {
            $price = Mage::helper('udquote2sale')->getVendorPriceByIds($row->getId(), Mage::helper('udquote2sale')->getVendorId());
            $row->setPrice($price);
        }
        return parent::render($row);
    }
}
