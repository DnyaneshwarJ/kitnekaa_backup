<?php


class Unirgy_DropshipVendorProduct_Model_SystemConfig_Backend_SyQuickCreateConfig extends Mage_Core_Model_Config_Data
{
    public function setValue($value)
    {
        $value = $this->_unserialize($value);
        if (is_array($value)) {
            unset($value['$$ROW']);
            $colDef = array(
                'fields_extra'=>array(),
                'required_fields'=>array(),
            );
            foreach (array('columns_def')
                as $colKey
            ) {
                if (is_array(@$value[$colKey])) {
                    unset($value[$colKey]['$ROW']);
                    usort($value[$colKey], array($this, 'sortBySortOrder'));
                    foreach ($value[$colKey] as $r) {
                       $colDef[substr($colKey,0,-4)][] = $r['column_field'];
                       $colDef['fields_extra'][$r['column_field']] = array();
                       if (!empty($r['is_required'])) {
                           $colDef['required_fields'][] = $r['column_field'];
                       }
                    }
                }
            }
            $value = array_merge($value, $colDef);
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

    public function sortBySortOrder($a, $b)
    {
        if ($a['sort_order']<$b['sort_order']) {
            return -1;
        } elseif ($a['sort_order']>$b['sort_order']) {
            return 1;
        }
        return 0;
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
