<?php

class Unirgy_DropshipVendorProduct_Model_ProductStatus extends Mage_Catalog_Model_Product_Status
{
    const STATUS_PENDING    = 3;
    const STATUS_FIX        = 4;
    const STATUS_DISCARD    = 5;
    const STATUS_VACATION   = 6;
    const STATUS_SUSPENDED   = 7;
    static public function getOptionArray()
    {
        $res = array(
            self::STATUS_ENABLED    => Mage::helper('udropship')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('udropship')->__('Disabled'),
            self::STATUS_PENDING    => Mage::helper('udropship')->__('Pending'),
            self::STATUS_FIX        => Mage::helper('udropship')->__('Fix'),
            self::STATUS_DISCARD    => Mage::helper('udropship')->__('Discard'),
            self::STATUS_VACATION   => Mage::helper('udropship')->__('Vacation')
        );
        if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorMembership')) {
            $res[self::STATUS_SUSPENDED] = Mage::helper('udropship')->__('Suspended');
        }
        return $res;
    }
    static public function getAllOptions()
    {
        $res = array(
            array(
                'value' => '',
                'label' => Mage::helper('udropship')->__('-- Please Select --')
            )
        );
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
               'value' => $index,
               'label' => $value
            );
        }
        return $res;
    }
}