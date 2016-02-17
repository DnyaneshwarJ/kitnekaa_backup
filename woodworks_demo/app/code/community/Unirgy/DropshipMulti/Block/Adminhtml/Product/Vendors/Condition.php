<?php

class Unirgy_DropshipMulti_Block_Adminhtml_Product_Vendors_Condition
    extends Mage_Adminhtml_Block_Abstract
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!Mage::helper('udropship')->isModuleActive('Unirgy_DropshipMicrosite')
            || !Mage::helper('umicrosite')->getCurrentVendor())
        {
            $this->getLayout()->getBlock('product_tabs')
                ->addTab('udmulti_vendors', 'udmulti/adminhtml_product_vendors');
        }
    }
}