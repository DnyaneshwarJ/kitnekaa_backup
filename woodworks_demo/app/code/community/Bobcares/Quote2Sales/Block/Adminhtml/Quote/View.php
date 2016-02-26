<?php
/**
 * Quote Block for Viewing a quote
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

	public function __construct(){
	            
        $this->_objectId = 'quote_id';
        $this->_blockGroup = 'quote2sales';
        $this->_controller = 'adminhtml_quote';
        $this->_mode        = 'view';
        parent::__construct();
	
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->setId('adminhtml_quote_view');
        
        $quote = $this->getQuote();

        if ($this->_isAllowedAction('cancel')) {
            $message = Mage::helper('quote2sales')->__('Are you sure you want to cancel this quote?');

            $this->_addButton('quote_cancel', array(
                'label' => Mage::helper('quote2sales')->__('Cancel Quote'),
                'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getCancelUrl() . '\')',
            ));
        }

        //If the quotetoorder action is allowed then display the button 'Quote to Order'
        if ($this->_isAllowedAction('quotetoorder')) {
            $this->_addButton('quote_to_order', array(
                'label' => Mage::helper('quote2sales')->__('Quote to Order'),
                'onclick' => 'setLocation(\'' . $this->getQuoteToOrderUrl() . '\')',
            ));
        }

      /*  if ($this->_isAllowedAction('edit')) {
        	$onclickJs = 'deleteConfirm(\''
        			. Mage::helper('quote2sales')->__('Are you sure? This quote will be canceled and a new one will be created instead')
        			. '\', \'' . $this->getEditUrl() . '\');';
        	$this->_addButton('quote_edit', array(
        			'label'    => Mage::helper('quote2sales')->__('Edit'),
        			'onclick'  => $onclickJs,
        	));
        }*/
      /*  if ($this->_isAllowedAction('duplicate')) {
        	
        	$this->_addButton('quote_duplicate', array(
        			'label'    => Mage::helper('quote2sales')->__('Reuse Quote'),
        			'onclick'  => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
        	));
        }*/
	}
  
	public function getHeaderText()
    {
        return Mage::helper('quote2sales')->__('View Quote');
    }
    
  /**
     * Retrieve quote model object
     */
    public function getQuote()
    {
        return Mage::registry('sales_quote');
    }   
    
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    
    }   
    
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('quote2sales/quote/actions/' . $action);
    }
  public function getEditUrl()
    {
        return $this->getUrl('*/adminhtml_quote_edit/edit', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
    public function getDuplicateUrl()
    {
    	return $this->getUrl('*/adminhtml_quote_edit/duplicate', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
    
     /**
     * @desc This function is used for redirecting the call
     * @return type call the mail function with the object id
     */
    public function getQuoteToOrderUrl() {      
        
        return $this->getUrl('*/adminhtml_quote_edit/quoteToOrder', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }
}