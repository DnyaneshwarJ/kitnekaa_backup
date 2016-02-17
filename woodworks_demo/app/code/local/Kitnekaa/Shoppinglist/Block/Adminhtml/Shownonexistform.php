<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Shownonexistform extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $customer_id = $this->getRequest()->getParam('customer_id');
        $list_id = $this->getRequest()->getParam('list_id');    
        $this->_objectId = 'nonexistformid';
        $this->_blockGroup = 'shoppinglist';
        $this->_controller = 'adminhtml';
        $this->_mode = 'shownonexistform';
        $this->_removeButton('back');
        $this->_removeButton('save');
         $this->_addButton('back', array(
        'label' => Mage::helper('shoppinglist')->__('Back'),
        'onclick' => "setLocation('" . $this->getUrl('*/shoppinglist/edit', array('customer_id' => $customer_id,'list_id'=>$list_id)). "')",
        'class' => 'back'
    ),-1,1); 
    }
 
    public function getHeaderText()
    {
        return Mage::helper('shoppinglist')->__('Add Not Existing Product');
    }
}
