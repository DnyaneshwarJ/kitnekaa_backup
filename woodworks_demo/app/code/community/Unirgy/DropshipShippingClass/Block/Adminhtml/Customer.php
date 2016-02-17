<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_Customer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udshipclass';
        $this->_controller = 'adminhtml_customer';
        $this->_headerText = Mage::helper('udropship')->__('Customer Ship Classes');
        parent::__construct();
    }

}
