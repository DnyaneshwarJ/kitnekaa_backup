<?php
class Bobcares_Quote2Sales_Model_Quote extends Mage_Sales_Model_Quote_Config
{
	  const STATE_ACTIVE	= 1;
    const STATUS_INACTIVE	= 0;
    
  /*
     * Get all the states which we can display for the customer
     */
   static public function getStates()
    {
        return array(
            self::STATE_ACTIVE   => Mage::helper('quote2sales')->__('Active'),
        	self::STATUS_INACTIVE   => Mage::helper('quote2sales')->__('Inactive')
           );
    }
    
    public function setInactive($quote_id){
    	try {
    	$quotes = Mage::getModel('sales/quote')->getCollection();
    	$quotes->addFieldToFilter('entity_id', $quote_id);
    	
    	//$quote->setIsActive(false);
   		if ($quotes->walk("delete")) return true; 
    		
    	} catch (Exception $e) {
    		Mage::log($e->getMessage());
    	}
    }
}