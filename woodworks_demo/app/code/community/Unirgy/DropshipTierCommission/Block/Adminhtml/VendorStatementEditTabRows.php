<?php

class Unirgy_DropshipTierCommission_Block_Adminhtml_VendorStatementEditTabRows extends Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_Edit_Tab_Rows
{
    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    => Mage::helper('udropship')->__('SKU'),
            'index'     => 'sku'
        ));
        $this->addColumn('vendor_sku', array(
            'header'    => Mage::helper('udropship')->__('Vendor SKU'),
            'index'     => 'vendor_sku'
        ));
        $this->addColumn('product', array(
            'header'    => Mage::helper('udropship')->__('Product'),
            'index'     => 'product'
        ));
        $this->addColumnsOrder('sku', 'po_increment_id');
        $this->addColumnsOrder('sku', 'vendor_sku');
        $this->addColumnsOrder('product', 'sku');
        return parent::_prepareColumns();
    }
}