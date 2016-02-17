<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Shoplistform extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {   $customer_id =  $this->getRequest()->getParam('customer_id');
        parent::__construct();
                  
        $this->_objectId = 'shoplistid';
        $this->_blockGroup = 'shoppinglist';
        $this->_controller = 'adminhtml';
        $this->_mode = 'shoplistform';
        $this->_removeButton('back');
        $this->_removeButton('save');
        $this->_addButton('back', array(
        'label' => Mage::helper('shoppinglist')->__('Back'),
        'onclick' => "setLocation('" . $this->getUrl('*/customer/edit', array('id' => $customer_id,'active_tab'=>'edit_customer_shoppinglist')). "')",
        'class' => 'back'
    ),-1,1);  
        
    }
 
    public function getHeaderText()
    {
        return Mage::helper('shoppinglist')->__('Add Shopping List');
    }

}
