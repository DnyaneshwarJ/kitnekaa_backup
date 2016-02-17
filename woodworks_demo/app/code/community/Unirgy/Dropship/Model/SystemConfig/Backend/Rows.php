<?php

class Unirgy_Dropship_Model_SystemConfig_Backend_Rows extends Mage_Core_Model_Config_Data
{
    public function setValue($value)
    {
        $value = $this->_unserialize($value);
        if (is_array($value)) {
            unset($value['$ROW']);
        }
        $this->setData('value', $value);
        return $this;
    }
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setData('value', $this->_unserialize($value));
        }
    }

    protected function _beforeSave()
    {
        if (is_array($this->getValue())) {
            $this->setData('value', $this->_serialize($this->getValue()));
        }
    }

    protected function _serialize($value)
    {
        return Mage::helper('udropship')->serialize($value);
    }
    protected function _unserialize($value)
    {
        return Mage::helper('udropship')->unserialize($value);
    }
}
