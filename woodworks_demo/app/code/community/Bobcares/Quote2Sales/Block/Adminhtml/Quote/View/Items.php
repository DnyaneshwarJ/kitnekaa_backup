<?php

/**
 * Quote Block for Viewing items in a quote
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    /**
     * Retrieve required options from parent
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
    }

    /**
     * Retrieve order items collection
     *
     * @return unknown
     */
    public function getItemsCollection()
    {
        return $this->getQuote()->getItemsCollection();
    }
    
  /**
     * Retrieve rendered item html content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getItemHtml(Varien_Object $item)
    {
        if ($item->getOrderItem()) {
            $type = $item->getOrderItem()->getProductType();
        } else {
            $type = $item->getProductType();
        }        
        return $this->getItemRenderer($type)
            ->setItem($item)
            ->toHtml();
    }
    public function getQuote(){
    	return Mage::registry("current_quote");
    }

}
