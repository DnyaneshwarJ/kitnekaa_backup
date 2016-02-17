<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Totals extends  Mage_Adminhtml_Block_Sales_Order_Create_Totals{
//extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract{
 public function __construct()
    {
        parent::__construct();
        $this->setId('quote2sales_quote_create_totals');
    }

    public function getHeaderText()
    {
        return Mage::helper('quote2sales')->__('Quote Totals');
    }
}