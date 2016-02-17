<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Customer_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid{
//extends Mage_Adminhtml_Block_Widget_Grid{

    public function __construct()
    {
        parent::__construct();
        $this->setId('quote2sales_quote_create_customer_grid');
        $this->setRowClickCallback('order.selectCustomer.bind(order)');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
    }
	
}