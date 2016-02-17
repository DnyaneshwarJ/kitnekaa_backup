<?php

class Unirgy_DropshipVendorPromotions_Block_Adminhtml_PromoGrid extends Mage_Adminhtml_Block_Promo_Quote_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumnAfter('udropship_vendor', array(
            'header'    => Mage::helper('udropship')->__('Dropship Vendor'),
            'align'     => 'left',
            'index'     => 'udropship_vendor',
            'type'      => 'options',
            'options'   => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash()
        ), 'is_active');
        return parent::_prepareColumns();
    }
}