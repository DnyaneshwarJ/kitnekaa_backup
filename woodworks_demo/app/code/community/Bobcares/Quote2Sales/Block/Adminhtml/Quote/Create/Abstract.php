<?php

//abstract class Mage_Adminhtml_Block_Sales_Order_Create_Abstract extends Mage_Adminhtml_Block_Widget
abstract class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Abstract extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract//Mage_Adminhtml_Block_Widget
{
    /**
     * Retrieve create order model object
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function getCreateOrderModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    /**
     * Retrieve quote session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }


}
