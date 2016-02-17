<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Search extends Mage_Adminhtml_Block_Sales_Order_Create_Search{
//extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract{
    public function __construct()
    {
        parent::__construct();
        $this->setId('quote2sales_quote_create_search');
    }
 	public function getButtonsHtml()
    {
        $addButtonData = array(
            'label' => Mage::helper('quote2sales')->__('Add Selected Product(s) to Quote'),
            'onclick' => 'order.productGridAddSelected()',
            'class' => 'add',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addButtonData)->toHtml();
    }
}