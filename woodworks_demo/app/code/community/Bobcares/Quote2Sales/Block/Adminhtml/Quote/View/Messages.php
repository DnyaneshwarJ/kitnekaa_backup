<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Messages extends Mage_Adminhtml_Block_Messages
{

    public function _getQuote()
    {
        return Mage::registry('sales_quote');
    }

    public function _prepareLayout()
    {
        /**
         * Check customer existing
         */
    	Mage::log($this->_getQuote());
    	
//        $customer = Mage::getModel('customer/customer')->load($this->_getQuote()->getCustomerId());

        /**
         * Check Item products existing
         */
   //     $productIds = array();
  //      foreach ($this->_getOrder()->getAllItems() as $item) {
  //          $productIds[] = $item->getProductId();
  //      }

        return parent::_prepareLayout();
    }

}
