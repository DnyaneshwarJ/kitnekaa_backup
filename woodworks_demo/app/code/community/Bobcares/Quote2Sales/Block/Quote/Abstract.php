<?php
class Bobcares_Quote2Sales_Block_Quote_Abstract extends Mage_Core_Block_Template{
	/**
     * Retrieve current quote model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::registry('current_quote');
    }

    public function getQuoteId(){
    	return $this->getQuote()->getData("entity_id");
    }
    	/**
     * Get formated price value including quote currency rate to  website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    protected function formatPrice($price, $addBrackets = false)
    {
        return $this->helper("quote2sales")->formatPrice($price, $addBrackets); 
    }
    
    protected function formatBasePrice($price, $addBrackets = false){
    //    return Mage::getModel("sales/order")->formatBasePrice($price);
        return $this->helper("quote2sales")->formatBasePrice($price, $addBrackets); 
    }
 
}
