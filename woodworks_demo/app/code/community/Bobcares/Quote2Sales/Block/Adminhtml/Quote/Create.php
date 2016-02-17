<?php
/**
 * Quote Block for Creating a quote
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Create extends Mage_Adminhtml_Block_Sales_Order_Create{
	
	public function __construct(){
		    
        parent::__construct();
        $this->_objectId = 'quote_id';
        $this->_blockGroup = 'quote2sales';
        $this->_controller = 'adminhtml_quote_create';
        $this->_mode        = 'create';

	
        $this->setId('adminhtml_quote_create');
        $customerId = $this->_getSession()->getCustomerId();
        $storeId    = $this->_getSession()->getStoreId();
        $quote_id = $this->_getSession()->getQuoteId();
        
        //        $quote = $this->getQuote();
        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Save Quote'));
        $this->_updateButton('save', 'onclick', "order.submit()");
        $this->_updateButton('save', 'id', 'submit_order_top_button');
        if (is_null($customerId) || !$storeId) {
            $this->_updateButton('save', 'style', 'display:none');
        }
        
        $this->_updateButton('back', 'id', 'back_order_top_button');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

        /*$this->_updateButton('reset', 'id', 'reset_order_top_button');

        if (is_null($customerId)) {
            $this->_updateButton('reset', 'style', 'display:none');
        } else {
            $this->_updateButton('back', 'style', 'display:none');
        }
*/        $confirm = Mage::helper('sales')->__('Are you sure you want to cancel this quote?');
        $this->_updateButton('reset', 'label', Mage::helper('sales')->__('Cancel'));
        $this->_updateButton('reset', 'class', 'cancel');
        $this->_updateButton('reset', 'onclick', 'deleteConfirm(\''.$confirm.'\', \'' . $this->getCancelUrl($quote_id) . '\')');
        
	}

	
    public function getCancelUrl($quoteId = "")
    {
        //if ($this->_getSession()->getQuote()->getId()) {
        //    $url = $this->getUrl('*/'.$this->_controller.'/view', array(
        //        'quote_id' => Mage::getSingleton('adminhtml/session_quote')->getQuote()->getId()
        //    ));
       // } else {
       if ($quoteId)
            $url = $this->getUrl('*/'.$this->_controller.'/cancel', array($this->_objectId =>$quoteId));
       else $url = $this->getUrl('*/'.$this->_controller.'/cancel');
        //}

        return $url;
    }
	
    public function getHeaderHtml()
    {
        $out = '<div id="order-header">'
            . $this->getLayout()->createBlock('quote2sales/adminhtml_quote_create_header')->toHtml()
            . '</div>';
        return $out;
    }
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
    	return $this->getUrl('*/adminhtml_quote/');
    }
}
