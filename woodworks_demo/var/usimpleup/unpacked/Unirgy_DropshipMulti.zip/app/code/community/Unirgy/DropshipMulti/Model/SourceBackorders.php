<?php

class Unirgy_DropshipMulti_Model_SourceBackorders extends Mage_CatalogInventory_Model_Source_Backorders
{
    public function toOptionArray()
    {
        $hlpm = Mage::helper('udmulti');
        $options = parent::toOptionArray();
        $options[] = array(
            'value' => 10,
            'label' => Mage::helper('udropship')->__('Use Avail State/Date to Allow Qty Below 0')
        );
        $options[] = array(
            'value' => 11,
            'label' => Mage::helper('udropship')->__('Use Avail State/Date to Allow Qty Below 0 and Notify Customer')
        );
        return $options;
    }
}