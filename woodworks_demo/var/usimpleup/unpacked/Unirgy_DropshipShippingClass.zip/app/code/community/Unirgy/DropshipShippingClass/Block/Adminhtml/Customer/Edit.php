<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_Customer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'udshipclass';
        $this->_controller  = 'adminhtml_customer';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Save Customer Ship Class'));
        $this->_updateButton('delete', 'label', Mage::helper('udropship')->__('Delete Customer Ship Class'));
    }

    public function getHeaderText()
    {
        if (Mage::registry('udshipclass_customer')->getId()) {
            return Mage::helper('udropship')->__("Edit Customer Ship Class '%s'", $this->htmlEscape(Mage::registry('udshipclass_customer')->getClassName()));
        }
        else {
            return Mage::helper('udropship')->__('New Customer Ship Class');
        }
    }

}
