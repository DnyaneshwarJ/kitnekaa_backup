<?php

/**
 * Block Functions needed by the Quote View template
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Quote_Items extends Mage_Sales_Block_Items_Abstract
{

    /**
     * Initialize default item renderer
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addItemRender('default', 'checkout/cart_item_renderer', 'checkout/cart/item/default.phtml');
    }
    /**
     * Retrieve current quote model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::registry('current_quote');
    }
    
/**
     * Rewrite Get item row html specifically for the layout we want. i.e the order layout
     *
     * @param   Varien_Object $item
     * @return  string
     */
    public function getItemHtml(Varien_Object $item)
    {
        $type = $this->_getItemType($item);
		//$type = $item->getProductType();        
		$block = $this->getItemRenderer($type)
            ->setItem($item);
        $this->_prepareItem($block);
        return $block->toHtml();
    }
}
