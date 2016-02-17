<?php

class Bobcares_Quote2Sales_Block_Request_History extends Bobcares_Quote2Sales_Block_Quote_Abstract{

	public function __construct()
    {
        parent::__construct();
       // $this->setTemplate('bobcares/quote2sales/history.phtml');
        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId(); 
        $requests = Mage::getResourceModel('quote2sales/request_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer_id)
            ->setOrder('created_at', 'desc');
        $this->setRequests($requests);		
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('quote2sales')->__('My Requests'));
    }

    protected function _prepareLayout(){
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'quote2sales.quote.history.pager')
            ->setCollection($this->getRequests());
        $this->setChild('pager', $pager);
        $this->getRequests()->load();
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    
    public function getQuoteUrl($quote_id)
    {
        return $this->getUrl('quote2sales/quote/view', array('quote_id' => $quote_id));
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
    
    
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
    public function getCommentDisplayLength(){
    	return 100; 
    } 
}
