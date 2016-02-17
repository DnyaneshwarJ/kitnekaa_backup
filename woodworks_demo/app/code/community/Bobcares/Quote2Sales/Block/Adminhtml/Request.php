<?php
/**
 *Block functions for RFQ List
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */

class Bobcares_Quote2Sales_Block_Adminhtml_Request extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_request';
    	$this->_blockGroup = 'quote2sales';
        $this->_headerText = Mage::helper('quote2sales')->__('Requests');
     //   $this->_addButtonLabel = Mage::helper('quote2sales')->__('Create New Quote');
        parent::__construct();
     //   if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_removeButton('add');
      //  }
    }
  //  public function getCreateUrl()
   // {
   //     return $this->getUrl('*/adminhtml_quote_create/');
  //  }
    
    
}
