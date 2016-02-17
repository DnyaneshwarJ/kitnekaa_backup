<?php

class Neo_AdminFormUpload_Block_Adminhtml_Csv_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'neo_adminformupload';
        $this->_controller = 'adminhtml_csv';
        $this->_updateButton('save', 'label', Mage::helper('neo_adminformupload')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('neo_adminformupload')->__('Delete'));
    }

    public function getHeaderText()
    {
        return Mage::helper('neo_adminformupload')->__('CSV Upload Module');
    }
}