<?php


class Unirgy_DropshipVendorProduct_Model_SystemConfig_Backend_Empty extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $this->setValue('');
        return parent::_beforeSave();
    }
}
