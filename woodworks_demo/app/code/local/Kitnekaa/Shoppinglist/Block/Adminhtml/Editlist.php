<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Editlist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $customer_id =  $this->getRequest()->getParam('customer_id');
    $list_id =  $this->getRequest()->getParam('list_id');
    $list_name = Mage::helper('shoppinglist')->findshoplistname($list_id);
    $this->_blockGroup = 'shoppinglist';
    $this->_controller = 'adminhtml_editlist';
    $this->_headerText = Mage::helper('shoppinglist')->__(' Shopping List - '.$list_name);
    //$this->_addButtonLabel = Mage::helper('shoppinglist')->__('Add product');
    parent::__construct();
     $this->_addButton('add', array(
        'label' => Mage::helper('shoppinglist')->__('Add Item'),
        'onclick' => "setLocation('" . $this->getUrl('*/*/additem', array('list_id' => $list_id,'customer_id' => $customer_id)). "')",
        'class' => 'add'.$customer_id
    ));
     
     $this->_addButton('addnonexisting', array(
        'label' => Mage::helper('shoppinglist')->__('Add Non Existing'),
        'onclick' => "setLocation('" . $this->getUrl('*/*/shownonexistform', array('list_id' => $list_id,'customer_id' => $customer_id)). "')",
        'class' => 'add'
    )); 

     $this->_addButton('back', array(
        'label' => Mage::helper('shoppinglist')->__('Back'),
        'onclick' => "setLocation('" . $this->getUrl('*/customer/edit', array('id' => $customer_id,'active_tab'=>'edit_customer_shoppinglist')). "')",
        'class' => 'back'
    ),-1,1);  
  }
}
