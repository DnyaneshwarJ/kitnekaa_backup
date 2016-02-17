<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Customer extends Mage_Adminhtml_Block_Sales_Order_Create_Customer{
//extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract{

    public function __construct()
    {
        parent::__construct();
        $this->setId('quote2sales_quote_create_customer');
        
    }
    

    public function getButtonsHtml()
    {
    	$addButtonData = array(
    			'label'     => Mage::helper('quote2sales')->__('Create New Customer'),
    		//	'onclick'   => 'order.setCustomerId(false)',
    			'onclick'	=> 'setLocation(\'' . Mage::helper("adminhtml")->getUrl("adminhtml/customer/new"). '\')',
    			'class'     => 'add',
    	);
    	return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addButtonData)->toHtml();
    }

    protected function _getSession()
    {
    	return Mage::getSingleton('adminhtml/session_quote');
    }
   
}