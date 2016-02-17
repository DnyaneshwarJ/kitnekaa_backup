<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_Vendor extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'udshipclass';
        $this->_controller = 'adminhtml_vendor';
        $this->_headerText = Mage::helper('udropship')->__('Vendor Ship Classes');
        parent::__construct();
    }

}
