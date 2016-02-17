<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Header extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Abstract//Mage_Adminhtml_Block_Sales_Order_Create_Header
//extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected function _toHtml()
    {
        if ($this->_getSession()->getQuote()->getId()) {
            return '<h3 class="icon-head head-sales-order">'.Mage::helper('quote2sales')->__('Edit Quote #%s', $this->_getSession()->getQuote()->getId()).'</h3>';
        }

        $customerId = $this->getCustomerId();
        $storeId    = $this->getStoreId();
        
        $out = '';
        if ($customerId && $storeId) {
            $out.= Mage::helper('quote2sales')->__('Create New Quote for %s in %s', $this->getCustomer()->getName(), $this->getStore()->getName());
        }
        elseif (!is_null($customerId) && $storeId){
            $out.= Mage::helper('quote2sales')->__('Create New Quote for New Customer in %s', $this->getStore()->getName());
        }
        elseif ($customerId) {
            $out.= Mage::helper('quote2sales')->__('Create New Quote for %s', $this->getCustomer()->getName());
        }
        elseif (!is_null($customerId)){
            $out.= Mage::helper('quote2sales')->__('Create New Quote for New Customer');
        }
        else {
            $out.= Mage::helper('quote2sales')->__('Create New Quote');
        }
        $out = $this->htmlEscape($out);
        $out = '<h3 class="icon-head head-sales-order">' . $out . '</h3>';
        return $out;
    }
}
