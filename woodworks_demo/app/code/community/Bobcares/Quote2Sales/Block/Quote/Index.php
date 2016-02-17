<?php

class Bobcares_Quote2Sales_Block_Quote_Index extends Bobcares_Quote2Sales_Block_Quote_Abstract{

   protected function _prepareLayout(){
        parent::_prepareLayout();
    }
    
    public function getUserName()
    {
    	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
    		return '';
    	}
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	return trim($customer->getName());
    }
    
    public function getUserEmail()
    {
    	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
    		return '';
    	}
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	return $customer->getEmail();
    }
}