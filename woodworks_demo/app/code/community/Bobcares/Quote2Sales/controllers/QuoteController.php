<?php

class Bobcares_Quote2Sales_QuoteController extends Mage_Core_Controller_Front_Action
{
	/**
     * Check quote view availability
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  bool
     */
    protected function _canViewOrder($quote)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
	    if ($quote->getId() && $quote->getCustomerId() && ($quote->getCustomerId() == $customerId)) {
            return true;
        }
        return false;
    }
    
	/*
	 * Gets the session 
	 * @return Mage_Customer_Model_Session
	 */
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }

    public function preDispatch(){
        parent::preDispatch();
        if (! $this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /*
     * Display the index page of quote history
     */
    public function historyAction(){
    	$comment = $this->getRequest()->getParam('comment');
    	 
	        $this->loadLayout();
	        $this->_initLayoutMessages('customer/session');
	        $this->renderLayout();
	}

    /*
     * Display the index page of quote information
     */
    public function infoAction(){
	        $this->loadLayout();
	        $this->_initLayoutMessages('customer/session');
	        $this->renderLayout();
	}
	
	public function viewAction(){
		$this->_viewAction();
	}
	
	/*
	 * Saves the Quote into the customer's cart and proceeds to checkout
	 */
	public function  checkoutAction(){
		    
        $quoteId = (int) $this->getRequest()->getParam('quote_id');
		$quote = Mage::getModel('sales/quote')->load($quoteId);
		
		if ($quote){
			$cartModel = Mage::getModel("checkout/cart"); 
		    $cartModel->setQuote($quote);
		   	$cartModel->saveQuote();
		}
		$this->_redirect('checkout/onepage');
		
	}
	
/**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidQuote()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('quote2sales/quote/history');
        }
        $this->renderLayout();
    }
    
/**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _totalsAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->renderLayout();
    }
      /**
     * Try to load valid quote by quote_id and register it
     *
     * @param int $quoteId
     * @return bool
     */
    protected function _loadValidQuote($quoteId = null)
    {
        if (null === $quoteId) {
            $quoteId = (int) $this->getRequest()->getParam('quote_id');
        }
        
        if (!$quoteId) {
            $this->_forward('noRoute');
            return false;
        }

        $quote = Mage::getModel('sales/quote')->load($quoteId);
		
        if ($this->_canViewOrder($quote)) {
            Mage::register('current_quote', $quote);
            return true;
        } else {
            $this->_redirect('*/*/history');
        }
        return false;
    }
    
}