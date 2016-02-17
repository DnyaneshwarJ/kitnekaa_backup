<?php

class Bobcares_Quote2Sales_Block_Quote_History extends Bobcares_Quote2Sales_Block_Quote_Abstract{

	public function __construct()
    {
        parent::__construct();
       // $this->setTemplate('bobcares/quote2sales/history.phtml');
        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId(); 
        $quotes = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer_id)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('items_qty', 
		    		array("neq" => 0))
            ->setOrder('created_at', 'desc')
        ;
        $this->setQuotes($quotes);		
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('quote2sales')->__('My Quotes'));
    }

    protected function _prepareLayout(){
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'quote2sales.quote.history.pager')
            ->setCollection($this->getQuotes());
        $this->setChild('pager', $pager);
        $this->getQuotes()->load();
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    
    public function getViewUrl($quote)
    {
        return $this->getUrl('*/*/view', array('quote_id' => $quote->getId()));
    }

	/**
     * Returns the quote ID
     * @param Mage_Sales_Model_Quote
     * @return int
     */
    public function getQuoteIdFromQuote(Mage_Sales_Model_Quote $quote)
    {
        $id = $quote->getData('entity_id');
      return $id;
    }
    
    
    public function getTrackUrl($order)
    {
        return $this->getUrl('*/*/track', array('order_id' => $order->getId()));
    }

    public function getReorderUrl($order)
    {
        return $this->getUrl('*/*/reorder', array('order_id' => $order->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
