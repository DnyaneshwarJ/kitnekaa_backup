<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Items_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid{
//extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract{
	
	
	public function __construct()
    {
        parent::__construct();
        $this->setId('quote2sales_quote_create_search_grid');
    }

    public function getItems()
    {
        $items = $this->getParentBlock()->getItems();
        $oldSuperMode = $this->getQuote()->getIsSuperMode();
        $this->getQuote()->setIsSuperMode(false);
        foreach ($items as $item) {
            $item->setQty($item->getQty());
            $stockItem = $item->getProduct()->getStockItem();
            if ($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                // This check has been performed properly in Inventory observer, so it has no sense
                /*
                $check = $stockItem->checkQuoteItemQty($item->getQty(), $item->getQty(), $item->getQty());
                $item->setMessage($check->getMessage());
                $item->setHasError($check->getHasError());
                */
                if ($item->getProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                    $item->setMessage(Mage::helper('adminhtml')->__('This product is currently disabled.'));
                    $item->setHasError(true);
                }
            }
        }

        $this->getQuote()->setIsSuperMode($oldSuperMode);
        return $items;
    }

    public function getSubtotal()
    {
        $address = $this->getQuoteAddress();
        if ($this->displayTotalsIncludeTax()) {
        	if ($address->getSubtotalInclTax()) {
                return $address->getSubtotalInclTax();
            }
            return $address->getSubtotal()+$address->getTaxAmount();
        } else {
        	return $address->getSubtotal();
        }
        return false;
    }

}