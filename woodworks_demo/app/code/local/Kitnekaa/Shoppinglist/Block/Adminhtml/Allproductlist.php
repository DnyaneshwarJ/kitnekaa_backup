<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Allproductlist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {  
    $customer_id = $this->getRequest()->getParam('customer_id');
    $list_id =  $this->getRequest()->getParam('list_id');
    $list_name = Mage::helper('shoppinglist')->findshoplistname($list_id);
    $this->_blockGroup = 'shoppinglist';
    $this->_controller = 'adminhtml_allproductlist';
    $this->_headerText = Mage::helper('shoppinglist')->__('Add Product -'.' '.$list_name);
  
      parent::__construct();
      $this->_removeButton('add');
      $this->_addButton('back', array(
            'label' => Mage::helper('shoppinglist')->__('Back'),
            'onclick' => "setLocation('" . $this->getUrl('*/shoppinglist/edit/', array('customer_id' => $customer_id, 'list_id' => $list_id)) . "')",
            'class' => 'back'
        ));  
  }
}
