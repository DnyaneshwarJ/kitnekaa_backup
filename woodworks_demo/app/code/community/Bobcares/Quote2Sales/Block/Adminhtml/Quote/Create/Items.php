<?php

class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Items extends Mage_Adminhtml_Block_Sales_Order_Create_Items {

//extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract{

    public function __construct() {
        parent::__construct();
        $this->setId('quote2sales_quote_create_items');
        $this->loadSelectedItemsToQuote();
    }

    public function getHeaderText() {
        return Mage::helper('quote2sales')->__('Items Quoted');
    }

    /**
     * @desc Function add the quote item while creating quote 
     */
    protected function loadSelectedItemsToQuote() {

        $productIdToAdd = Mage::getModel('quote2sales/request')->getCollection()
                        ->addFieldToselect('product_id')
                        ->addFieldToFilter('request_id', ((int) $this->getRequest()->getParam('request_id')))
                        ->getFirstItem()->getData('product_id');

        /* If the product specific RFQ is genrated */
        if ($productIdToAdd) {
            $this->getQuote()->removeAllItems();
            $this->getQuote()->save();
            $product = Mage::getModel('catalog/product')->load($productIdToAdd);
            $quoteItem = $this->getQuote()->addProduct($product);
            $this->getQuote()->collectTotals();
            $this->getQuote()->save();
        }
    }

}
