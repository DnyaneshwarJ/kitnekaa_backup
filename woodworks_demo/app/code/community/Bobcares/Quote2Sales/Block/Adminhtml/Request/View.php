<?php
/**
 * Block for Viewing a request
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Request_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

	public function __construct(){
	            
        $this->_objectId = 'id';
        $this->_blockGroup = 'quote2sales';
        $this->_controller = 'adminhtml_quote';
        $this->_mode        = 'view';
        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->setId('adminhtml_request_view');
        
    /*    $request = $this->getRequest();
	
      //  if ($this->_isAllowedAction('cancel')) {
        	
        	$this->_addButton('quote_create', array(
        			'label'     => Mage::helper('quote2sales')->__('Convert to Quote'),
        			'onclick'   => 'setLocation(\'' . $this->getCreateQuoteUrl() . '\')',
        	));
      //  }
        if ($this->_isAllowedAction('edit')) {
        	$onclickJs = 'deleteConfirm(\''
        			. Mage::helper('quote2sales')->__('Are you sure? This quote will be canceled and a new one will be created instead')
        			. '\', \'' . $this->getEditUrl() . '\');';
        	$this->_addButton('quote_edit', array(
        			'label'    => Mage::helper('quote2sales')->__('Edit'),
        			'onclick'  => $onclickJs,
        	));
        }
        if ($this->_isAllowedAction('duplicate')) {
        	
        	$this->_addButton('quote_duplicate', array(
        			'label'    => Mage::helper('quote2sales')->__('Reuse Quote'),
        			'onclick'  => 'setLocation(\'' . $this->getDuplicateUrl() . '\')',
        	));
        }
        */
	}
  
	public function getHeaderText()
    {
        return Mage::helper('quote2sales')->__('View Request');
    }
    
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    
    }   
    
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('quote2sales/request/actions/' . $action);
    }
    public function getCreateQuoteUrl()
    {
    	return $this->getUrl('*/adminhtml_quote_create/index', array("request_id" => $this->getRequest()->getRequest_id(), "customer_id" => $this->getRequest()->getCustomer_id()));
    }
}