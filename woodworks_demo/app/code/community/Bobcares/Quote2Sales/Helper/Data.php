<?php
class Bobcares_Quote2Sales_Helper_Data extends Mage_Core_Helper_Abstract{
	

	/**
     * Get formated price value including quote currency rate to  website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return Mage::getModel("sales/order")->formatPrice($price, $addBrackets);
    }
    
    public  function formatBasePrice($price){
        return Mage::getModel("sales/order")->formatBasePrice($price);
    }
	 public function getQuote()
    {
        return Mage::registry('current_quote');
    }
    
        public function isCurrencyDifferent()
    {
    	$quote = $this->getQuote();
        return $quote->getQuoteCurrencyCode() != $quote->getBaseCurrencyCode();
    }
    
    /*
     * Gets all the Customers
    * @return array of Customers array(email => name);
    */
    public function getAllCustomers(){
    
    	$collection = Mage::getModel('customer/customer')
    	->getCollection()->addNameToSelect();
    	//$collection = Mage::getResourceModel('customer/customer_collection')
    	//  ->addNameToSelect()
    	//  ->addAttributeToSelect('email');
    	//$this->setCollection($collection);
    	$customers = array();
    	foreach ($collection as $customer){
    		$customer_id = $customer->getId();
    		$name=$customer->getName();
    		$customers[$customer_id]= $name;
    	}
    	return $customers;
    }
    
    /*
     * Gets the stored config from the admin settings for Quote2Sales
     */
    private function getConfig($config_path){
    	try {
	    	$config_value = Mage::getStoreConfig($config_path, Mage::app()->getStore()->getId());
	    	if (empty($config_value)) throw new Exception("Quote2Sales settings not correct. Please contact support");    		
	    	return $config_value;
    	} catch (Exception $e) {
    		Mage::log($config_path . " is empty.");
    		throw $e;
    	}
	}
    /*
     * Processes values and prices of checkbox into array
     * @param $checkbox_values - an array of all the values of the checkbox. i.e individual checkboxes
     * @param $entered_value - string of option ids selected by user. eg 2,3
     * @return array(array(sku=>"", $price=xx), ....)
     */
    private function processCheckbox($checkbox_values = array(), $entered_value=""){
    	
    	// Make the entered string into an array. 	    		
    	$entered_value_array = explode(",", $entered_value);
    	$entered_checkbox_values = array();
	    
    	foreach ($checkbox_values as $checkbox_value){
	    	
	    	$checkbox_option_type_id = $checkbox_value->getData("option_type_id");
	    	if (in_array($checkbox_option_type_id, $entered_value_array))
	    	{
	    		$checkbox_option_sku = $checkbox_value->getData("sku");
	    		//$checkbox_option_price = $checkbox_value->getPrice();
	    				
		    	// Add the selected checkbox details into the array		
	    		array_push($entered_checkbox_values,   
		    				array("sku"			=> $checkbox_option_sku));
	    	}
	    }
	    return $entered_checkbox_values;
    }
    
    /*
     * ceil to nearest 10, and deduct the round
     */
	private function ceilpow10($val) {
	   $round = $this->getConfig($this->round);
		//First round to the nearest integer
		$val = ceil($val);
		if ($val % 10 == 0) return $val;
	   	// round to nearest 10;
		return ($val + (10 - ($val % 10)) - $round);
	}
}