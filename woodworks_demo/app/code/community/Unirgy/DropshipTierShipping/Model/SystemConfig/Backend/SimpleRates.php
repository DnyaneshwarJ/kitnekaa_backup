<?php


class Unirgy_DropshipTierShipping_Model_SystemConfig_Backend_SimpleRates extends Mage_Core_Model_Config_Data
{
    public function setValue($value)
    {
        if (is_array($value)) {
            unset($value['$ROW']);
            unset($value['$$ROW']);
        }
        $this->setData('value', $value);
        return $this;
    }
    protected function _beforeSave()
    {
        $value = $this->getValue();
        Mage::helper('udtiership')->saveV2SimpleRates($value);
        $this->setValue('');
        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
    }

    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }
}
