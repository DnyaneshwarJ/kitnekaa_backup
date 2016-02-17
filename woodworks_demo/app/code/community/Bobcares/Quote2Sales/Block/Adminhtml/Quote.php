<?php
/**
 *Block functions for Quote List
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */

//class Bobcares_Quote2Sales_Block_Sales_Order extends Mage_Adminhtml_Block_Sales_Order
class Bobcares_Quote2Sales_Block_Adminhtml_Quote extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_quote';
    	$this->_blockGroup = 'quote2sales';
        $this->_headerText = Mage::helper('quote2sales')->__('Quotes');
        $this->_addButtonLabel = Mage::helper('quote2sales')->__('Create New Quote');
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('quote2sales/quote/actions/create')) {
            $this->_removeButton('add');
        }
    }
    public function getCreateUrl()
    {
        return $this->getUrl('*/adminhtml_quote_create/');
    }
    

}
