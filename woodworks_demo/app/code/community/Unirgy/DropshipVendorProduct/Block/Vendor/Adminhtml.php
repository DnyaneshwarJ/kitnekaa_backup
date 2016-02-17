<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Adminhtml extends Mage_Adminhtml_Block_Template
{
    protected $_oldStoreId;
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        return $this;
    }
    protected function _afterToHtml($html)
    {
        Mage::helper('udropship/catalog')->setDesignStore();
        Mage::app()->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }
}