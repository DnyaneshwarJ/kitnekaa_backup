<?php

class Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Quote_Create_Items extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create_Items
{
    /**
     * @desc Function add the quote item while creating quote
     */
    protected function loadSelectedItemsToQuote()
    {
        $products = Mage::getModel('quote2salescustom/requestproducts')->getCollection()
            ->addFieldToselect('product_id')
            ->addFieldToFilter('request_id', ((int)$this->getRequest()->getParam('request_id')));
        if(count($products->getData())) {
            $this->getQuote()->removeAllItems();
            $this->getQuote()->save();
            foreach ($products as $item) {
                if ($item->getProductId()) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                  /* $additionalOptions = array();
                    if ($additionalOption = $product->getCustomOption('additional_options'))
	                {
                        $additionalOptions = (array)
                        $additionalOptions = (array) unserialize($additionalOption->getValue());
                    }
                    $additionalOptions[]=array('label'=>'Demo','value'=>1);
                    $product->addCustomOption('additional_options', serialize($additionalOptions));*/

                    /*$option = array('udropship_vendor'=>Mage::helper('udquote2sale')->getVendorId());
                    $request = new Varien_Object();
                    $request->setData($option);*/
                    $this->getQuote()->addProduct($product,new Varien_Object(array('udropship_vendor'=>Mage::helper('udquote2sale')->getVendorId())));
                }

            }
            $this->getQuote()->collectTotals();
            $quote_id = $this->getQuote()->save()->getId();
            Mage::getSingleton('adminhtml/session_quote')->setCurrentQuoteId($quote_id);
        }
    }
}
