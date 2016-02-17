<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Search_Grid_Renderer_Price extends
    Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Price
{
    /**
     * Render minimal price for downloadable products
     *
     * @param   Varien_Object $row
     * @return  string
     */
   /* public function render(Varien_Object $row)
    {
        if ($row->getTypeId() == 'downloadable') {
            $row->setPrice($row->getPrice());
        }
        return parent::render($row);
    }
   */
}
