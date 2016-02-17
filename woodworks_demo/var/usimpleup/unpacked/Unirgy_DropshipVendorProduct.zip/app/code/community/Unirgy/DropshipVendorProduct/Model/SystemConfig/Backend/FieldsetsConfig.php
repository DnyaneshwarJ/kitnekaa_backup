<?php


class Unirgy_DropshipVendorProduct_Model_SystemConfig_Backend_FieldsetsConfig extends Mage_Core_Model_Config_Data
{
    public function setValue($value)
    {
        $value = $this->_unserialize($value);
        if (is_array($value)) {
            unset($value['$$ROW']);
            usort($value, array($this, 'sortBySortOrder'));
            foreach ($value as &$_val) {
                $colDef = array(
                    'fields_extra'=>array(),
                    'required_fields'=>array(),
                );
                foreach (array('top_columns_def','bottom_columns_def','left_columns_def','right_columns_def')
                    as $colKey
                ) {
                    if (is_array(@$_val[$colKey])) {
                        unset($_val[$colKey]['$ROW']);
                        usort($_val[$colKey], array($this, 'sortBySortOrder'));
                        foreach ($_val[$colKey] as $r) {
                           $colDef[substr($colKey,0,-4)][] = $r['column_field'];
                           $colDef['fields_extra'][$r['column_field']] = array(
                               'use_limit_type' => @$r['use_limit_type'],
                               'limit_type' => @$r['limit_type'],
                           );
                           if (!empty($r['is_required'])) {
                               $colDef['required_fields'][] = $r['column_field'];
                           }
                        }
                    }
                }
                $_val = array_merge($_val, $colDef);
            }
            unset($_val);
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
